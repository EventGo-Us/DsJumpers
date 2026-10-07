#!/bin/sh
# Starts php-fpm and nginx in one container.
#
# No process supervisor on purpose: if either process dies the container
# exits and ECS replaces the task (fail fast instead of half-alive).
set -eu

# 1. App config: env vars injected by ECS from SSM -> .env (+ files)
php /usr/local/bin/write-dotenv.php

# The values now live in .env; drop them from the environment so they are not
# inherited by nginx or php-fpm.
for key in $(echo "${DOTENV_KEYS:-}" | tr ',' ' '); do
    unset "$key"
done
unset DOTENV_KEYS

# 2. Start both processes
php-fpm --nodaemonize &
FPM_PID=$!

nginx -e /dev/stderr -g 'daemon off;' &
NGINX_PID=$!

STOPPING=0
shutdown() {
    STOPPING=1
    # QUIT = graceful: finish in-flight requests, then exit
    kill -QUIT "$NGINX_PID" "$FPM_PID" 2>/dev/null || true
}
trap shutdown TERM INT QUIT

# 3. Wait until either process exits
while kill -0 "$FPM_PID" 2>/dev/null && kill -0 "$NGINX_PID" 2>/dev/null; do
    sleep 1 &
    wait $! || true
done

if [ "$STOPPING" -eq 0 ]; then
    echo "[entrypoint] php-fpm or nginx exited unexpectedly, stopping the container" >&2
    shutdown
    STOPPING=0
fi

wait "$FPM_PID" 2>/dev/null || true
wait "$NGINX_PID" 2>/dev/null || true

# Non-zero on a crash so ECS reports the task as failed
[ "$STOPPING" -eq 1 ]
