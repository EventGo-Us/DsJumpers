<?php
    ob_start();
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); 
    }
    include_once __DIR__ . '/../config/config.php';     
    include_once __DIR__ . '/../config/database.php';    
    $database = new Database();
    $db = $database->getConnection();

// Procesar la solicitud si viene por AJAX
if (isset($_GET['buscar']) && strlen(trim($_GET['buscar'])) >= 10 ) {
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    
    
    $countryCode = 'us';
    if ($db) {
        try {
            $sql = "SELECT Pais FROM account LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);    

            if ($account && !empty($account['Pais'])) {
                $pais = strtoupper(trim($account['Pais']));
                if ($pais === 'USA' || $pais === 'US') {
                    $countryCode = 'us';
                } elseif ($pais === 'MEXICO' || $pais === 'MÉXICO' || $pais === 'MX') {
                    $countryCode = 'mx';
                } else {
                    $countryCode = strtolower(substr($pais, 0, 2));
                }
            }
        } catch (Exception $e) {
            // Continuar con el valor por defecto si falla la consulta
        }
    }

    $searchValue = trim($_GET['buscar']);
    if ($db) {
        $localResults = [];
        $seenAddresses = [];

        foreach (['customers', 'venues'] as $tableName) {
            try {
                $columnRows = $db->query("SHOW COLUMNS FROM `{$tableName}`")->fetchAll(PDO::FETCH_COLUMN);
                $availableColumns = array_flip($columnRows);

                $fields = ['Direccion'];
                foreach (['Direccion2', 'Ciudad', 'CP', 'Estado', 'Pais', 'Lat', 'Lng'] as $field) {
                    if (isset($availableColumns[$field])) {
                        $fields[] = $field;
                    }
                }

                $selectFields = implode(', ', array_map(function ($field) {
                    return '`' . $field . '`';
                }, $fields));

                $searchFields = [];
                foreach (['Direccion', 'Direccion2', 'Ciudad', 'CP', 'Estado', 'Pais'] as $field) {
                    if (isset($availableColumns[$field])) {
                        $searchFields[] = "`{$field}`";
                    }
                }
                $fullAddressExpression = 'CONCAT_WS(\' \', ' . implode(', ', $searchFields) . ')';

                $searchWords = preg_split('/\s+/', $searchValue, -1, PREG_SPLIT_NO_EMPTY);
                $conditions = [];
                $parameters = [];
                foreach (array_slice($searchWords, 0, 6) as $index => $word) {
                    $paramName = ':term' . $index;
                    $conditions[] = "{$fullAddressExpression} LIKE {$paramName}";
                    $parameters[$paramName] = '%' . $word . '%';
                }
                if (empty($conditions)) {
                    continue;
                }

                $stmtLocal = $db->prepare(
                    "SELECT {$selectFields}
                       FROM `{$tableName}`
                      WHERE (" . implode(' AND ', $conditions) . ")
                      LIMIT 20"
                );
                $stmtLocal->execute($parameters);

                while ($row = $stmtLocal->fetch(PDO::FETCH_ASSOC)) {
                    $road = trim((string)($row['Direccion'] ?? ''));
                    $suburb = trim((string)($row['Direccion2'] ?? ''));
                    $city = trim((string)($row['Ciudad'] ?? ''));
                    $postalCode = trim((string)($row['CP'] ?? ''));
                    $stateValue = trim((string)($row['Estado'] ?? ''));
                    $countryValue = trim((string)($row['Pais'] ?? ''));
                    $lat = (string)($row['Lat'] ?? '');
                    $lng = (string)($row['Lng'] ?? '');

                    $stateForDropdown = $stateValue;
                    $stateId = $stateValue;
                    if ($stateValue !== '') {
                        try {
                            $stmtState = $db->prepare(
                                "SELECT Id, Estado
                                   FROM estados_pais
                                  WHERE Id = :state_id OR Estado = :state_name
                                  LIMIT 1"
                            );
                            $stmtState->execute([
                                ':state_id' => $stateValue ,
                                ':state_name' => $stateValue
                            ]);
                            $stateRow = $stmtState->fetch(PDO::FETCH_ASSOC);
                            if ($stateRow) {
                                $stateId = (string)$stateRow['Id'];
                                $stateForDropdown = $stateRow['Estado'];
                            }
                        } catch (Exception $e) {
                            // Mantener el valor original si no puede resolverse.
                        }
                    }

                    $displayParts = array_filter([$road, $suburb, $city, $postalCode, $stateForDropdown, $countryValue]);
                    $displayName = implode(', ', $displayParts);
                    $dedupeKey = mb_strtolower($displayName, 'UTF-8');
                    if ($displayName === '' || isset($seenAddresses[$dedupeKey])) {
                        continue;
                    }
                    $seenAddresses[$dedupeKey] = true;

                    $localResults[] = [
                        'display_name' => $displayName,
                        'lat' => $lat,
                        'lon' => $lng,
                        'lng' => $lng,
                        'place_id' => 'local-' . substr(hash('sha256', $tableName . '|' . $dedupeKey), 0, 32),
                        'address' => [
                            'road' => $road,
                            'house_number' => '',
                            'country' => $countryValue,
                            'country_code' => strtolower(substr($countryValue, 0, 2)),
                            'city' => $city,
                            'town' => $city,
                            'suburb' => $suburb,
                            'village' => '',
                            'state' => $stateForDropdown,
                            'state_id' => $stateId,
                            'state_code' => '',
                            'postcode' => $postalCode
                        ]
                    ];
                }
            } catch (PDOException $e) {
                error_log("Address lookup: {$tableName} search error: " . $e->getMessage());
            }
        }

        if (!empty($localResults)) {
            header('Cache-Control: private, max-age=300');
            echo json_encode($localResults, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    $normalizedQuery = function_exists('mb_strtolower')
        ? mb_strtolower(trim($_GET['buscar']), 'UTF-8')
        : strtolower(trim($_GET['buscar']));
    $cacheKey = hash('sha256', $countryCode . '|' . $normalizedQuery);

    if ($db) {
        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS google_geocode_cache (
                    cache_key CHAR(64) NOT NULL,
                    country_code VARCHAR(10) NOT NULL,
                    search_query VARCHAR(255) NOT NULL,
                    response_data LONGTEXT NOT NULL,
                    expires_at DATETIME NOT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (cache_key),
                    KEY idx_google_geocode_expiration (expires_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $stmtCache = $db->prepare(
                "SELECT response_data
                   FROM google_geocode_cache
                  WHERE cache_key = :cache_key
                    AND expires_at > NOW()
                  LIMIT 1"
            );
            $stmtCache->execute([':cache_key' => $cacheKey]);
            $cached = $stmtCache->fetch(PDO::FETCH_ASSOC);

            if ($cached) {
                $cachedData = json_decode($cached['response_data'], true);
                if (is_array($cachedData)) {
                    header('Cache-Control: private, max-age=300');
                    echo json_encode($cachedData, JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
        } catch (PDOException $e) {
            error_log('Google geocode cache read error: ' . $e->getMessage());
        }
    }

    $apiKey = $_SESSION['google_api'] ?? '';
    if (empty($apiKey)) {
        echo json_encode(['error' => 'GOOGLE_API_KEY no configurada']);
        exit;
    }

    $query = urlencode(trim($_GET['buscar']));
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$query}";
    if (!empty($countryCode)) {
        $url .= "&components=country:{$countryCode}";
    }
    $url .= "&key={$apiKey}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        
        if (isset($data['status']) && ($data['status'] === 'OK' || $data['status'] === 'ZERO_RESULTS')) {
            $formattedResults = [];
            
            if ($data['status'] === 'OK' && !empty($data['results'])) {
                foreach ($data['results'] as $result) {
                    $streetNumber = '';
                    $route = '';
                    $locality = '';
                    $sublocality = '';
                    $adminArea2 = '';
                    $adminArea1 = '';
                    $adminArea1Short = '';
                    $countryName = '';
                    $countryShort = '';
                    $postalCode = '';

                    foreach ($result['address_components'] ?? [] as $comp) {
                        $types = $comp['types'] ?? [];
                        if (in_array('street_number', $types)) {
                            $streetNumber = $comp['long_name'];
                        }
                        if (in_array('route', $types)) {
                            $route = $comp['long_name'];
                        }
                        if (in_array('locality', $types)) {
                            $locality = $comp['long_name'];
                        }
                        if (in_array('sublocality', $types) || in_array('sublocality_level_1', $types)) {
                            $sublocality = $comp['long_name'];
                        }
                        if (in_array('administrative_area_level_2', $types)) {
                            $adminArea2 = $comp['long_name'];
                        }
                        if (in_array('administrative_area_level_1', $types)) {
                            $adminArea1 = $comp['long_name'];
                            $adminArea1Short = $comp['short_name'];
                        }
                        if (in_array('country', $types)) {
                            $countryName = $comp['long_name'];
                            $countryShort = $comp['short_name'];
                        }
                        if (in_array('postal_code', $types)) {
                            $postalCode = $comp['long_name'];
                        }
                    }

                    $lat = $result['geometry']['location']['lat'] ?? '';
                    $lng = $result['geometry']['location']['lng'] ?? '';
                    $city = !empty($locality) ? $locality : (!empty($sublocality) ? $sublocality : (!empty($adminArea2) ? $adminArea2 : ''));

                    // Normalizar estado para compatibilidad con el selector de estados
                    $stateForDropdown = $adminArea1;
                    $stateId = '';
                    if (!empty($adminArea1)) {
                        $cleanState = strtoupper(strtr($adminArea1, [
                            'Á'=>'A', 'É'=>'E', 'Í'=>'I', 'Ó'=>'O', 'Ú'=>'U', 'Ü'=>'U', 'Ñ'=>'N',
                            'á'=>'A', 'é'=>'E', 'í'=>'I', 'ó'=>'O', 'ú'=>'U', 'ü'=>'U', 'ñ'=>'N'
                        ]));
                        
                        if ($db) {
                            try {
                                $paisCode = ($countryShort === 'US' || strtoupper($countryName) === 'UNITED STATES') ? 'USA' : 'MX';
                                $stmtSt = $db->prepare("SELECT Id, Estado FROM estados_pais WHERE CodigoPais = :pais AND (Estado = :estado OR Estado LIKE :like1) LIMIT 1");
                                $stmtSt->execute([':pais' => $paisCode, ':estado' => $cleanState, ':like1' => '%' . $cleanState . '%']);
                                $rowSt = $stmtSt->fetch(PDO::FETCH_ASSOC);
                                if ($rowSt) {
                                    $stateId = (string)$rowSt['Id'];
                                    $stateForDropdown = $rowSt['Estado'];
                                } else {
                                    $stateForDropdown = $cleanState;
                                }
                            } catch (Exception $e) {
                                $stateForDropdown = $cleanState;
                            }
                        } else {
                            $stateForDropdown = $cleanState;
                        }
                    }

                    $formattedResults[] = [
                        'display_name' => $result['formatted_address'] ?? '',
                        'lat' => (string)$lat,
                        'lon' => (string)$lng,
                        'lng' => (string)$lng,
                        'place_id' => $result['place_id'] ?? '',
                        'address' => [
                            'road' => $route,
                            'house_number' => $streetNumber,
                            'country' => $countryName,
                            'country_code' => strtolower($countryShort),
                            'city' => $city,
                            'town' => $locality,
                            'suburb' => $sublocality,
                            'village' => $adminArea2,
                            'state' => $stateForDropdown,
                            'state_id' => $stateId,
                            'state_code' => $adminArea1Short,
                            'postcode' => $postalCode
                        ]
                    ];
                }
            }
            
            $cacheData = $formattedResults;
            if ($db) {
                try {
                    $stmtCache = $db->prepare(
                        "INSERT INTO google_geocode_cache
                            (cache_key, country_code, search_query, response_data, expires_at)
                         VALUES
                            (:cache_key, :country_code, :search_query, :response_data, DATE_ADD(NOW(), INTERVAL 24 HOUR))
                         ON DUPLICATE KEY UPDATE
                            response_data = VALUES(response_data),
                            expires_at = VALUES(expires_at),
                            search_query = VALUES(search_query)"
                    );
                    $stmtCache->execute([
                        ':cache_key' => $cacheKey,
                        ':country_code' => $countryCode,
                        ':search_query' => substr(trim($_GET['buscar']), 0, 255),
                        ':response_data' => json_encode($cacheData, JSON_UNESCAPED_UNICODE)
                    ]);

                    if (random_int(1, 100) === 1) {
                        $db->exec("DELETE FROM google_geocode_cache WHERE expires_at < NOW()");
                    }
                } catch (PDOException $e) {
                    error_log('Google geocode cache write error: ' . $e->getMessage());
                }
            }
            header('Cache-Control: private, max-age=300');
            echo json_encode($cacheData, JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['error' => 'Google Maps API: '.($data['status'] ?? 'DESCONOCIDO')]);
        }
    } else {
        echo json_encode(['error' => 'No se pudo conectar con el servicio de Google Maps' . ($curlError ? ": $curlError" : '')]);
    }
    exit;
}
?>