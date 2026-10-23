<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_GET['lang'])) {
    $_SESSION['Idioma'] = $_GET['lang'];
}
$Idioma = $_SESSION['Idioma'] ?? 'es';
$isEn = (strtolower(substr(trim($Idioma), 0, 2)) === 'en');

$t = [
    'page_title' => $isEn ? 'Recursive pricing function' : 'Función recursiva de precios',
    'funcion_precios' => $isEn ? 'Pricing function' : 'Función de precios',
    'funcion_precios_edit' => $isEn ? 'Pricing function (Edit)' : 'Función de precios (Edit)',
    'funcion_precios_third' => $isEn ? 'Pricing function (3)' : 'Función de precios (3)',
    'limpiar' => $isEn ? 'Clear' : 'Limpiar',
    'precio_vs_tiempo' => $isEn ? 'Price vs Time' : 'Precio vs Tiempo',
    'precio_vs_tiempo_edit' => $isEn ? 'Price vs Time (Edit)' : 'Precio vs Tiempo (Edit)',
    'precio_vs_tiempo_third' => $isEn ? 'Price vs Time (3)' : 'Precio vs Tiempo (3)',
    'horas' => $isEn ? 'Hours' : 'Horas',
    'dias' => $isEn ? 'Days' : 'Días',
    'semanas' => $isEn ? 'Weeks' : 'Semanas',
    'json_precio' => $isEn ? 'Price JSON' : 'JSON del precio',
    'json_precio_edit' => $isEn ? 'Price JSON (Edit)' : 'JSON del precio (Edit)',
    'json_precio_third' => $isEn ? 'Price JSON (3)' : 'JSON del precio (3)',
    'aplicar_json' => $isEn ? 'Apply JSON' : 'Aplicar JSON',
    'fecha_hora_inicio' => $isEn ? 'Start date and time' : 'Fecha y hora de inicio',
    'fecha_hora_termino' => $isEn ? 'End date and time' : 'Fecha y hora de término',
    'calcular' => $isEn ? 'Calculate' : 'Calcular',
    'precio_estancia' => $isEn ? 'Stay price:' : 'Precio de la estancia:',
    'error_fechas' => $isEn ? 'Enter valid dates. The end must be after the start.' : 'Ingresa fechas válidas. El término debe ser posterior al inicio.',
    'error_sin_precio' => $isEn ? 'Define the pricing steps before calculating.' : 'Define los pasos del precio antes de calcular.',
];
?>
<!DOCTYPE html>
<html lang="<?= $isEn ? 'en' : 'es' ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $t['page_title'] ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.caso, .edit_caso { border-left: 4px solid var(--bs-primary); }
</style>
</head>
<body class="bg-body-tertiary">
<div class="container py-4">

  <!-- ========================================== -->
  <!-- SECCIÓN PRINCIPAL (NUEVO / REGISTRO)      -->
  <!-- ========================================== -->
  <div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h4 mb-0"><?= $t['funcion_precios'] ?></h1>
      <button type="button" id="btnLimpiar" class="btn btn-outline-danger btn-sm"><?= $t['limpiar'] ?></button>
    </div>
    <div id="casos" class="vstack gap-2 mb-4"></div>
    <div class="card mb-4"><div class="card-body">
      <h2 class="h6 mb-3"><?= $t['precio_estancia'] ?></h2>
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label for="fechahorainicio" class="form-label"><?= $t['fecha_hora_inicio'] ?></label>
          <input type="datetime-local" id="fechahorainicio" class="form-control">
        </div>
        <div class="col-md-4">
          <label for="fechahoratermino" class="form-label"><?= $t['fecha_hora_termino'] ?></label>
          <input type="datetime-local" id="fechahoratermino" class="form-control">
        </div>
        <div class="col-md-4">
          <button type="button" id="btnCalcular" class="btn btn-primary"><?= $t['calcular'] ?></button>
        </div>
      </div>
      <div id="resultadoCalculo" class="mt-3" role="status" aria-live="polite"></div>
    </div></div>
    <div class="row g-3 mb-3">
      <div class="col-lg-12">
        <div class="card"><div class="card-body">
          <div class="d-flex justify-content-between mb-2">
            <h2 class="h6 mb-0"><?= $t['precio_vs_tiempo'] ?></h2>
            <select id="eje" class="form-select form-select-sm w-auto">
              <option value="1"><?= $t['horas'] ?></option>
              <option value="24"><?= $t['dias'] ?></option>
              <option value="168"><?= $t['semanas'] ?></option>
            </select>
          </div>
          <div style="height:320px"><canvas id="grafica"></canvas></div>
        </div></div>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-lg-12">
        <div class="card h-100"><div class="card-body d-flex flex-column">
          <div class="d-flex justify-content-between mb-2">
            <h2 class="h6 mb-0"><?= $t['json_precio'] ?></h2>
            <button type="button" id="btnAplicar" class="btn btn-sm btn-outline-primary"><?= $t['aplicar_json'] ?></button>
          </div>
          <textarea id="json" class="form-control font-monospace flex-grow-1" rows="10" spellcheck="false"></textarea>
          <div id="jsonErr" class="text-danger small mt-1"></div>
        </div></div>
      </div>
    </div>  
  </div>

  
<?php

function get_price(string $json, string $fechahorainicio, string $fechahoratermino): float
{
    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

    if (!isset($data['pasos']) || !is_array($data['pasos'])) {
        throw new InvalidArgumentException('El JSON debe incluir un arreglo "pasos".');
    }

    $inicio = new DateTimeImmutable($fechahorainicio);
    $termino = new DateTimeImmutable($fechahoratermino);
    $segundos = $termino->getTimestamp() - $inicio->getTimestamp();

    if ($segundos <= 0) {
        throw new InvalidArgumentException('La fecha de término debe ser posterior a la fecha de inicio.');
    }

    $horasEstancia = $segundos / 3600;
    $horasPorUnidad = [
        'hora' => 1,
        'horas' => 1,
        'dia' => 24,
        'dias' => 24,
        'semana' => 168,
        'semanas' => 168,
    ];

    $segmentos = [];
    $inicioSegmento = 0.0;

    foreach ($data['pasos'] as $paso) {
        if (!isset($paso['tipo'], $paso['caso'], $paso['precio'])) {
            throw new InvalidArgumentException('Cada paso debe incluir "caso", "tipo" y "precio".');
        }

        $finSegmento = INF;

        if ($paso['tipo'] === 'hasta') {
            $unidad = $paso['unidad'] ?? '';
            if (!isset($horasPorUnidad[$unidad]) || !isset($paso['cantidad'])) {
                throw new InvalidArgumentException('Unidad o cantidad inválida en un paso "hasta".');
            }

            $finSegmento = (float) $paso['cantidad'] * $horasPorUnidad[$unidad];
        }

        if ((int) $paso['caso'] === 2) {
            $unidad = $paso['cada']['unidad'] ?? '';
            $cantidad = $paso['cada']['cantidad'] ?? null;

            if (!isset($horasPorUnidad[$unidad]) || !is_numeric($cantidad) || (float) $cantidad <= 0) {
                throw new InvalidArgumentException('El paso "cada" debe tener una cantidad y unidad válidas.');
            }

            $segmentos[] = [
                'inicio' => $inicioSegmento,
                'fin' => $finSegmento,
                'precio' => (float) $paso['precio'],
                'periodo' => (float) $cantidad * $horasPorUnidad[$unidad],
            ];

            $inicioSegmento = $finSegmento;
        } elseif ($paso['tipo'] !== 'cada') {
            $segmentos[] = [
                'inicio' => $inicioSegmento,
                'fin' => $finSegmento,
                'precio' => (float) $paso['precio'],
                'periodo' => null,
            ];

            $inicioSegmento = $finSegmento;
        }
    }

    if ($segmentos === []) {
        throw new InvalidArgumentException('No hay pasos de precio para calcular.');
    }

    $total = 0.0;

    foreach ($segmentos as $segmento) {
        if ($horasEstancia <= $segmento['inicio']) {
            break;
        }

        $horasEnSegmento = min($horasEstancia, $segmento['fin']);

        if ($segmento['periodo'] !== null) {
            $periodos = ceil(
                ($horasEnSegmento - $segmento['inicio']) / $segmento['periodo'] - 1e-9
            );
            $total += $segmento['precio'] * $periodos;
        } else {
            $total += $segmento['precio'];
        }
    }

    return $total;
}

$jsonPrecio ='
{
  "pasos": [
    {
      "caso": 1,
      "precio": 100,
      "tipo": "cada",
      "cantidad": 1,
      "unidad": "dia"
    },
    {
      "caso": 2,
      "precio": 100,
      "cada": {
        "cantidad": 1,
        "unidad": "dia"
      },
      "tipo": "hasta",
      "cantidad": 1,
      "unidad": "dia"
    },
    {
      "caso": 3,
      "precio": 50,
      "despues_de": {
        "cantidad": 1,
        "unidad": "dia"
      },
      "tipo": "cada",
      "cantidad": 1,
      "unidad": "dia"
    },
    {
      "caso": 2,
      "precio": 50,
      "cada": {
        "cantidad": 1,
        "unidad": "dia"
      },
      "tipo": "indefinido"
    }
  ]
}
';
 echo "$". get_price(
    $jsonPrecio,
    '2026-10-22T10:00',
    '2026-10-22T14:30'
);

?>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const I18N = <?= json_encode([
    'units' => [
        'dia' => [$isEn ? 'day' : 'día', 24],
        'hora' => [$isEn ? 'hour' : 'hora', 1],
        'semana' => [$isEn ? 'week' : 'semana', 168],
        'dias' => [$isEn ? 'days' : 'días', 24],
        'horas' => [$isEn ? 'hours' : 'horas', 1],
        'semanas' => [$isEn ? 'weeks' : 'semanas', 168],
    ],
    'unit_placeholder' => $isEn ? 'Unit…' : 'Unidad…',
    'select_placeholder' => $isEn ? 'Select…' : 'Selecciona…',
    'qty_placeholder' => $isEn ? 'Qty.' : 'Cant.',
    'price_placeholder' => $isEn ? 'Price' : 'Precio',
    'case_badge' => $isEn ? 'Case' : 'Caso',
    'price_is' => $isEn ? 'The price is:' : 'El precio es:',
    'charge_prefix' => $isEn ? 'Charge' : 'Cargo',
    'per_each' => $isEn ? 'per each' : 'por cada',
    'after' => $isEn ? 'After' : 'Después de',
    'price_becomes' => $isEn ? 'the price is' : 'el precio es de',
    'modos' => [
        'indefinido' => $isEn ? 'Indefinite' : 'Indefinido',
        'hasta' => $isEn ? 'Until' : 'Hasta',
        'cada' => $isEn ? 'Every' : 'Cada',
    ],
    'err_greater' => $isEn ? 'Must be greater than ' : 'Debe ser mayor a ',
    'chart_price' => $isEn ? 'Price' : 'Precio',
    'chart_time_title' => $isEn ? 'Time' : 'Tiempo',
    'chart_price_title' => $isEn ? 'Price ($)' : 'Precio ($)',
    'err_missing_pasos' => $isEn ? 'Missing "pasos" array' : 'Falta el arreglo "pasos"',
    'err_invalid_json' => $isEn ? 'Invalid JSON: ' : 'JSON inválido: ',
    'stay_price' => $isEn ? 'Stay price:' : 'Precio de la estancia:',
    'error_dates' => $isEn ? 'Enter valid dates. The end must be after the start.' : 'Ingresa fechas válidas. El término debe ser posterior al inicio.',
    'error_no_price' => $isEn ? 'Define the pricing steps before calculating.' : 'Define los pasos del precio antes de calcular.',
], JSON_UNESCAPED_UNICODE) ?>;

const U = I18N.units;
const ALL = ['dia','hora','semana','dias','horas','semanas'], PL = ['dias','horas','semanas'];
const toH = (c,u) => c * U[u][1], lab = (c,u) => `${c} ${U[u][0]}`, fmt = h => `${+h.toFixed(2)} h`;

// ==========================================
// 1. FUNCIONALIDAD ORIGINAL
// ==========================================
let steps=[], stash={i:-1,tail:[]}, chart;

/* Convierte los pasos en segmentos de tiempo: {S inicio, E fin, P precio, N periodo (si es "por cada")} */
function segs(arr){
  const out=[]; let S=0;
  arr.forEach(s=>{
    const E=s.tipo==='hasta'?toH(s.cantidad,s.unidad):Infinity;
    if(s.caso===2){out.push({S,E,P:s.precio,N:toH(s.cada.cantidad,s.cada.unidad)});S=E;}
    else if(s.tipo!=='cada'){out.push({S,E,P:s.precio});S=E;}
  });
  return {out,S};
}
function price(sg,t){
  let a=0;
  for(const g of sg){
    if(t<=g.S)break;
    const tt=Math.min(t,g.E);
    a+=g.N?g.P*Math.ceil((tt-g.S)/g.N-1e-9):g.P;
  }
  return a;
}

function calculateStayPrice(requestJson){
  const request=JSON.parse(requestJson);
  const start=new Date(request.fechahorainicio);
  const end=new Date(request.fechahoratermino);
  if(!Number.isFinite(start.getTime())||!Number.isFinite(end.getTime())||end<=start){
    throw new Error(I18N.error_dates);
  }
  const {out}=segs(steps);
  if(!out.length)throw new Error(I18N.error_no_price);
  const hours=(end.getTime()-start.getTime())/3600000;
  return price(out,hours);
}

function sync(row,n){
  const m=row.find('.m').val(), show=m&&m!=='indefinido';
  row.find('.q,.u').toggleClass('d-none',!show);
  const list=(n===1&&m==='hasta')?PL:ALL, u=row.find('.u'), cur=u.val();
  u.html('<option value="">' + I18N.unit_placeholder + '</option>'+list.map(k=>`<option value="${k}">${U[k][0]}</option>`).join(''))
   .val(list.includes(cur)?cur:'');
}

function read(row,n,p){
  const s={caso:n}, m=row.find('.m').val();
  if(n===2){s.precio=p.precio;s.cada={cantidad:p.cantidad,unidad:p.unidad};}
  else{
    const pr=row.find('.pr').val(); if(pr===''||+pr<0)return null;
    s.precio=+pr; if(n===3)s.despues_de={cantidad:p.cantidad,unidad:p.unidad};
  }
  if(!m)return null; s.tipo=m;
  if(m!=='indefinido'){
    const q=+row.find('.q').val(), u=row.find('.u').val();
    if(!(q>0)||!u)return null; s.cantidad=q; s.unidad=u;
  }
  return s;
}

/* Valida el caso actual y, si está completo, detona (recursivamente) el siguiente */
function evaluate(i,row,n,p,over){
  let s=read(row,n,p), err='';
  if(s&&s.tipo==='hasta'){
    const S=segs(steps.slice(0,i)).S;
    if(toH(s.cantidad,s.unidad)<=S){err=I18N.err_greater+fmt(S);s=null;}
  }
  row.find('.err').text(err);
  if(over===undefined&&s&&steps[i]&&JSON.stringify(s)===JSON.stringify(steps[i]))return;
  const tail=over!==undefined?over:(steps.length>i+1?steps.slice(i+1):(stash.i===i?stash.tail:[]));
  if(over===undefined&&steps.length>i+1)stash={i,tail};
  row.nextAll().remove(); steps.length=i;
  if(s){steps[i]=s; if(s.tipo!=='indefinido')buildCase(i+1,tail);}
  if(over===undefined)refresh();
}

function buildCase(i,tail){
  tail=tail||[];
  const p=steps[i-1], n=i===0?1:(p.tipo==='cada'?2:3);
  let d=tail[0]; if(d&&d.caso!==n){d=null;tail=[];}
  const price=`<div class="input-group input-group-sm w-auto"><span class="input-group-text">$</span><input type="number" min="0" step="any" class="form-control pr" style="width:100px" placeholder="${I18N.price_placeholder}"></div>`;
  const pre=n===1?`<b>${I18N.price_is}</b>${price}`
    :n===2?`<span>${I18N.charge_prefix} <b>$${p.precio}</b> ${I18N.per_each} <b>${lab(p.cantidad,p.unidad)}</b></span>`
    :`<span>${I18N.after} <b>${lab(p.cantidad,p.unidad)}</b> ${I18N.price_becomes}</span>${price}`;
  const modos=(n===2?['indefinido','hasta']:['indefinido','hasta','cada'])
    .map(m=>`<option value="${m}">${I18N.modos[m] || (m[0].toUpperCase()+m.slice(1))}</option>`).join('');
  const row=$(`<div class="caso card card-body py-2 px-3"><div class="d-flex flex-wrap align-items-center gap-2">
    <span class="badge text-bg-primary">${I18N.case_badge} ${n}</span>${pre}
    <select class="form-select form-select-sm w-auto m"><option value="">${I18N.select_placeholder}</option>${modos}</select>
    <input type="number" min="0" step="any" class="form-control form-control-sm q d-none" style="width:90px" placeholder="${I18N.qty_placeholder}">
    <select class="form-select form-select-sm w-auto u d-none"></select>
    <span class="text-danger small err"></span></div></div>`).appendTo('#casos');
  sync(row,n);
  const go=()=>{sync(row,n);evaluate(i,row,n,p);};
  let t;
  row.on('change','select',go).on('input','input',()=>{clearTimeout(t);t=setTimeout(go,500);});
  if(d){
    if(n!==2)row.find('.pr').val(d.precio);
    row.find('.m').val(d.tipo); sync(row,n);
    row.find('.q').val(d.cantidad||''); row.find('.u').val(d.unidad||'');
    evaluate(i,row,n,p,tail.slice(1));
  }
}

function refresh(){
  $('#json').val(JSON.stringify({pasos:steps},null,2));
  const {out}=segs(steps), k=+$('#eje').val(), ds=chart.data.datasets[0];
  chart.options.scales.x.title.text=I18N.chart_time_title + ' ('+$('#eje option:selected').text().toLowerCase()+')';
  if(!out.length){ds.data=[];chart.update();return;}
  let base=0; out.forEach(g=>{base=Math.max(base,g.S,isFinite(g.E)?g.E:0);});
  let H=0;
  out.forEach(g=>{
    if(isFinite(g.E))H=Math.max(H,g.E*1.2);
    else H=Math.max(H,g.N?g.S+6*g.N:Math.max(base*1.5,24));
  });
  const ev=new Set([0,H]);
  out.forEach(g=>{
    if(g.S<=H)ev.add(g.S); if(isFinite(g.E)&&g.E<=H)ev.add(g.E);
    if(g.N)for(let j=1,x=g.S+g.N;x<H&&x<=g.E&&j<1500;j++,x=g.S+j*g.N)ev.add(x);
  });
  const eps=H/5000, pts=[];
  [...ev].sort((a,b)=>a-b).forEach(e=>{
    pts.push({x:e/k,y:price(out,e)});
    if(e<H)pts.push({x:(e+eps)/k,y:price(out,e+eps)});
  });
  ds.data=pts; chart.update();
}

function reset(tail){
  $('#casos').empty(); steps=[]; stash={i:-1,tail:[]};
  buildCase(0,tail); refresh();
}


// ==========================================
// INICIALIZACIÓN
// ==========================================
$(function(){
  $('form.needs-validation').on('submit',e=>e.preventDefault());
  // 1. Gráfica y eventos originales
  chart=new Chart($('#grafica'),{type:'line',
    data:{datasets:[{label:I18N.chart_price,data:[],borderColor:'#0d6efd',borderWidth:2,pointRadius:0,fill:false}]},
    options:{responsive:true,maintainAspectRatio:false,animation:false,parsing:false,
      plugins:{legend:{display:false}},
      scales:{x:{type:'linear',min:0,title:{display:true,text:I18N.chart_time_title}},
              y:{beginAtZero:true,title:{display:true,text:I18N.chart_price_title}}}}});
  $('#eje').on('change',refresh);
  $('#btnLimpiar').on('click',()=>{$('#jsonErr').text('');reset();});
  $('#btnCalcular').on('click',()=>{
    const payload={
      fechahorainicio:$('#fechahorainicio').val(),
      fechahoratermino:$('#fechahoratermino').val()
    };
    const result=$('#resultadoCalculo');
    try{
      const amount=calculateStayPrice(JSON.stringify(payload));
      result.removeClass('text-danger').addClass('fw-semibold')
        .text(`${I18N.stay_price} $${amount.toFixed(2)}`);
    }catch(e){
      result.removeClass('fw-semibold').addClass('text-danger').text(e.message);
    }
  });
  $('#btnAplicar').on('click',()=>{
    try{
      const o=JSON.parse($('#json').val());
      if(!Array.isArray(o.pasos))throw new Error(I18N.err_missing_pasos);
      $('#jsonErr').text(''); reset(o.pasos);
    }catch(e){$('#jsonErr').text(I18N.err_invalid_json + e.message);}
  });
  reset();

});
</script>
</body>
</html>