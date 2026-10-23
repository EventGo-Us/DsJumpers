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

  <hr class="my-5">

  <!-- ========================================== -->
  <!-- SECCIÓN DUPLICADA (EDITAR / EDICIÓN)       -->
  <!-- ========================================== -->
  <div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h4 mb-0"><?= $t['funcion_precios_edit'] ?></h1>
      <button type="button" id="edit_btnLimpiar" class="btn btn-outline-danger btn-sm"><?= $t['limpiar'] ?></button>
    </div>
    <div id="edit_casos" class="vstack gap-2 mb-4"></div>
    <div class="row g-3 mb-3">
      <div class="col-lg-12">
        <div class="card"><div class="card-body">
          <div class="d-flex justify-content-between mb-2">
            <h2 class="h6 mb-0"><?= $t['precio_vs_tiempo_edit'] ?></h2>
            <select id="edit_eje" class="form-select form-select-sm w-auto">
              <option value="1"><?= $t['horas'] ?></option>
              <option value="24"><?= $t['dias'] ?></option>
              <option value="168"><?= $t['semanas'] ?></option>
            </select>
          </div>
          <div style="height:320px"><canvas id="edit_grafica"></canvas></div>
        </div></div>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-lg-12">
        <div class="card h-100"><div class="card-body d-flex flex-column">
          <div class="d-flex justify-content-between mb-2">
            <h2 class="h6 mb-0"><?= $t['json_precio_edit'] ?></h2>
            <button type="button" id="edit_btnAplicar" class="btn btn-sm btn-outline-primary"><?= $t['aplicar_json'] ?></button>
          </div>
          <textarea id="edit_json" class="form-control font-monospace flex-grow-1" rows="10" spellcheck="false"></textarea>
          <div id="edit_jsonErr" class="text-danger small mt-1"></div>
        </div></div>
      </div>
    </div>  
  </div>

<form name="edit_<?php echo $TablePrices?>" id="edit_<?php echo $TablePrices?>" class="needs-validation" novalidate>
  <div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h4 mb-0"><?= $t['funcion_precios_third'] ?></h1>
      <button type="button" id="third_btnLimpiar" class="btn btn-outline-danger btn-sm"><?= $t['limpiar'] ?></button>
    </div>
    <div id="third_casos" class="vstack gap-2 mb-4"></div>
    <div class="row g-3 mb-3">
      <div class="col-lg-12">
        <div class="card"><div class="card-body">
          <div class="d-flex justify-content-between mb-2">
            <h2 class="h6 mb-0"><?= $t['precio_vs_tiempo_third'] ?></h2>
            <select id="third_eje" class="form-select form-select-sm w-auto">
              <option value="1"><?= $t['horas'] ?></option>
              <option value="24"><?= $t['dias'] ?></option>
              <option value="168"><?= $t['semanas'] ?></option>
            </select>
          </div>
          <div style="height:320px"><canvas id="third_grafica"></canvas></div>
        </div></div>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-lg-12">
        <div class="card h-100"><div class="card-body d-flex flex-column">
          <div class="d-flex justify-content-between mb-2">
            <h2 class="h6 mb-0"><?= $t['json_precio_third'] ?></h2>
            <button type="button" id="third_btnAplicar" class="btn btn-sm btn-outline-primary"><?= $t['aplicar_json'] ?></button>
          </div>
          <textarea id="third_json" class="form-control font-monospace flex-grow-1" rows="10" spellcheck="false"></textarea>
          <div id="third_jsonErr" class="text-danger small mt-1"></div>
        </div></div>
      </div>
    </div>
  </div>
</form>

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
// 2. FUNCIONALIDAD DUPLICADA (EDIT_)
// ==========================================
let edit_steps=[], edit_stash={i:-1,tail:[]}, edit_chart;

function edit_segs(arr){
  const out=[]; let S=0;
  arr.forEach(s=>{
    const E=s.tipo==='hasta'?toH(s.cantidad,s.unidad):Infinity;
    if(s.caso===2){out.push({S,E,P:s.precio,N:toH(s.cada.cantidad,s.cada.unidad)});S=E;}
    else if(s.tipo!=='cada'){out.push({S,E,P:s.precio});S=E;}
  });
  return {out,S};
}

function edit_price(sg,t){
  let a=0;
  for(const g of sg){
    if(t<=g.S)break;
    const tt=Math.min(t,g.E);
    a+=g.N?g.P*Math.ceil((tt-g.S)/g.N-1e-9):g.P;
  }
  return a;
}

function edit_sync(row,n){
  const m=row.find('.edit_m, .m').val(), show=m&&m!=='indefinido';
  row.find('.edit_q, .q, .edit_u, .u').toggleClass('d-none',!show);
  const list=(n===1&&m==='hasta')?PL:ALL, u=row.find('.edit_u, .u'), cur=u.val();
  u.html('<option value="">' + I18N.unit_placeholder + '</option>'+list.map(k=>`<option value="${k}">${U[k][0]}</option>`).join(''))
   .val(list.includes(cur)?cur:'');
}

function edit_read(row,n,p){
  const s={caso:n}, m=row.find('.edit_m, .m').val();
  if(n===2){s.precio=p.precio;s.cada={cantidad:p.cantidad,unidad:p.unidad};}
  else{
    const pr=row.find('.edit_pr, .pr').val(); if(pr===''||+pr<0)return null;
    s.precio=+pr; if(n===3)s.despues_de={cantidad:p.cantidad,unidad:p.unidad};
  }
  if(!m)return null; s.tipo=m;
  if(m!=='indefinido'){
    const q=+row.find('.edit_q, .q').val(), u=row.find('.edit_u, .u').val();
    if(!(q>0)||!u)return null; s.cantidad=q; s.unidad=u;
  }
  return s;
}

function edit_evaluate(i,row,n,p,over){
  let s=edit_read(row,n,p), err='';
  if(s&&s.tipo==='hasta'){
    const S=edit_segs(edit_steps.slice(0,i)).S;
    if(toH(s.cantidad,s.unidad)<=S){err=I18N.err_greater+fmt(S);s=null;}
  }
  row.find('.edit_err, .err').text(err);
  if(over===undefined&&s&&edit_steps[i]&&JSON.stringify(s)===JSON.stringify(edit_steps[i]))return;
  const tail=over!==undefined?over:(edit_steps.length>i+1?edit_steps.slice(i+1):(edit_stash.i===i?edit_stash.tail:[]));
  if(over===undefined&&edit_steps.length>i+1)edit_stash={i,tail};
  row.nextAll().remove(); edit_steps.length=i;
  if(s){edit_steps[i]=s; if(s.tipo!=='indefinido')edit_buildCase(i+1,tail);}
  if(over===undefined)edit_refresh();
}

function edit_buildCase(i,tail){
  tail=tail||[];
  const p=edit_steps[i-1], n=i===0?1:(p.tipo==='cada'?2:3);
  let d=tail[0]; if(d&&d.caso!==n){d=null;tail=[];}
  const price=`<div class="input-group input-group-sm w-auto"><span class="input-group-text">$</span><input type="number" min="0" step="any" class="form-control edit_pr pr" style="width:100px" placeholder="${I18N.price_placeholder}"></div>`;
  const pre=n===1?`<b>${I18N.price_is}</b>${price}`
    :n===2?`<span>${I18N.charge_prefix} <b>$${p.precio}</b> ${I18N.per_each} <b>${lab(p.cantidad,p.unidad)}</b></span>`
    :`<span>${I18N.after} <b>${lab(p.cantidad,p.unidad)}</b> ${I18N.price_becomes}</span>${price}`;
  const modos=(n===2?['indefinido','hasta']:['indefinido','hasta','cada'])
    .map(m=>`<option value="${m}">${I18N.modos[m] || (m[0].toUpperCase()+m.slice(1))}</option>`).join('');
  const row=$(`<div class="edit_caso caso card card-body py-2 px-3"><div class="d-flex flex-wrap align-items-center gap-2">
    <span class="badge text-bg-primary">${I18N.case_badge} ${n}</span>${pre}
    <select class="form-select form-select-sm w-auto edit_m m"><option value="">${I18N.select_placeholder}</option>${modos}</select>
    <input type="number" min="0" step="any" class="form-control form-control-sm edit_q q d-none" style="width:90px" placeholder="${I18N.qty_placeholder}">
    <select class="form-select form-select-sm w-auto edit_u u d-none"></select>
    <span class="text-danger small edit_err err"></span></div></div>`).appendTo('#edit_casos');
  edit_sync(row,n);
  const go=()=>{edit_sync(row,n);edit_evaluate(i,row,n,p);};
  let t;
  row.on('change','select',go).on('input','input',()=>{clearTimeout(t);t=setTimeout(go,500);});
  if(d){
    if(n!==2)row.find('.edit_pr, .pr').val(d.precio);
    row.find('.edit_m, .m').val(d.tipo); edit_sync(row,n);
    row.find('.edit_q, .q').val(d.cantidad||''); row.find('.edit_u, .u').val(d.unidad||'');
    edit_evaluate(i,row,n,p,tail.slice(1));
  }
}

function edit_refresh(){
  $('#edit_json').val(JSON.stringify({pasos:edit_steps},null,2));
  const {out}=edit_segs(edit_steps), k=+$('#edit_eje').val(), ds=edit_chart.data.datasets[0];
  edit_chart.options.scales.x.title.text=I18N.chart_time_title + ' ('+$('#edit_eje option:selected').text().toLowerCase()+')';
  if(!out.length){ds.data=[];edit_chart.update();return;}
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
    pts.push({x:e/k,y:edit_price(out,e)});
    if(e<H)pts.push({x:(e+eps)/k,y:edit_price(out,e+eps)});
  });
  ds.data=pts; edit_chart.update();
}

function edit_reset(tail){
  $('#edit_casos').empty(); edit_steps=[]; edit_stash={i:-1,tail:[]};
  edit_buildCase(0,tail); edit_refresh();
}

// ==========================================
// 3. FUNCIONALIDAD DENTRO DEL FORMULARIO
// ==========================================
let third_steps=[], third_stash={i:-1,tail:[]}, third_chart;

function third_segs(arr){
  const out=[]; let S=0;
  arr.forEach(s=>{
    const E=s.tipo==='hasta'?toH(s.cantidad,s.unidad):Infinity;
    if(s.caso===2){out.push({S,E,P:s.precio,N:toH(s.cada.cantidad,s.cada.unidad)});S=E;}
    else if(s.tipo!=='cada'){out.push({S,E,P:s.precio});S=E;}
  });
  return {out,S};
}

function third_price(sg,t){
  let a=0;
  for(const g of sg){
    if(t<=g.S)break;
    const tt=Math.min(t,g.E);
    a+=g.N?g.P*Math.ceil((tt-g.S)/g.N-1e-9):g.P;
  }
  return a;
}

function third_sync(row,n){
  const m=row.find('.m').val(), show=m&&m!=='indefinido';
  row.find('.q,.u').toggleClass('d-none',!show);
  const list=(n===1&&m==='hasta')?PL:ALL, u=row.find('.u'), cur=u.val();
  u.html('<option value="">' + I18N.unit_placeholder + '</option>'+list.map(k=>`<option value="${k}">${U[k][0]}</option>`).join(''))
   .val(list.includes(cur)?cur:'');
}

function third_read(row,n,p){
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

function third_evaluate(i,row,n,p,over){
  let s=third_read(row,n,p), err='';
  if(s&&s.tipo==='hasta'){
    const S=third_segs(third_steps.slice(0,i)).S;
    if(toH(s.cantidad,s.unidad)<=S){err=I18N.err_greater+fmt(S);s=null;}
  }
  row.find('.err').text(err);
  if(over===undefined&&s&&third_steps[i]&&JSON.stringify(s)===JSON.stringify(third_steps[i]))return;
  const tail=over!==undefined?over:(third_steps.length>i+1?third_steps.slice(i+1):(third_stash.i===i?third_stash.tail:[]));
  if(over===undefined&&third_steps.length>i+1)third_stash={i,tail};
  row.nextAll().remove(); third_steps.length=i;
  if(s){third_steps[i]=s; if(s.tipo!=='indefinido')third_buildCase(i+1,tail);}
  if(over===undefined)third_refresh();
}

function third_buildCase(i,tail){
  tail=tail||[];
  const p=third_steps[i-1], n=i===0?1:(p.tipo==='cada'?2:3);
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
    <span class="text-danger small err"></span></div></div>`).appendTo('#third_casos');
  third_sync(row,n);
  const go=()=>{third_sync(row,n);third_evaluate(i,row,n,p);};
  let t;
  row.on('change','select',go).on('input','input',()=>{clearTimeout(t);t=setTimeout(go,500);});
  if(d){
    if(n!==2)row.find('.pr').val(d.precio);
    row.find('.m').val(d.tipo); third_sync(row,n);
    row.find('.q').val(d.cantidad||''); row.find('.u').val(d.unidad||'');
    third_evaluate(i,row,n,p,tail.slice(1));
  }
}

function third_refresh(){
  $('#third_json').val(JSON.stringify({pasos:third_steps},null,2));
  const {out}=third_segs(third_steps), k=+$('#third_eje').val(), ds=third_chart.data.datasets[0];
  third_chart.options.scales.x.title.text=I18N.chart_time_title + ' ('+$('#third_eje option:selected').text().toLowerCase()+')';
  if(!out.length){ds.data=[];third_chart.update();return;}
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
    pts.push({x:e/k,y:third_price(out,e)});
    if(e<H)pts.push({x:(e+eps)/k,y:third_price(out,e+eps)});
  });
  ds.data=pts; third_chart.update();
}

function third_reset(tail){
  $('#third_casos').empty(); third_steps=[]; third_stash={i:-1,tail:[]};
  third_buildCase(0,tail); third_refresh();
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
  $('#btnAplicar').on('click',()=>{
    try{
      const o=JSON.parse($('#json').val());
      if(!Array.isArray(o.pasos))throw new Error(I18N.err_missing_pasos);
      $('#jsonErr').text(''); reset(o.pasos);
    }catch(e){$('#jsonErr').text(I18N.err_invalid_json + e.message);}
  });
  reset();

  // 2. Gráfica y eventos edit_
  edit_chart=new Chart($('#edit_grafica'),{type:'line',
    data:{datasets:[{label:I18N.chart_price,data:[],borderColor:'#0d6efd',borderWidth:2,pointRadius:0,fill:false}]},
    options:{responsive:true,maintainAspectRatio:false,animation:false,parsing:false,
      plugins:{legend:{display:false}},
      scales:{x:{type:'linear',min:0,title:{display:true,text:I18N.chart_time_title}},
              y:{beginAtZero:true,title:{display:true,text:I18N.chart_price_title}}}}});
  $('#edit_eje').on('change',edit_refresh);
  $('#edit_btnLimpiar').on('click',()=>{$('#edit_jsonErr').text('');edit_reset();});
  $('#edit_btnAplicar').on('click',()=>{
    try{
      const o=JSON.parse($('#edit_json').val());
      if(!Array.isArray(o.pasos))throw new Error(I18N.err_missing_pasos);
      $('#edit_jsonErr').text(''); edit_reset(o.pasos);
    }catch(e){$('#edit_jsonErr').text(I18N.err_invalid_json + e.message);}
  });
  edit_reset();

  third_chart=new Chart($('#third_grafica'),{type:'line',
    data:{datasets:[{label:I18N.chart_price,data:[],borderColor:'#0d6efd',borderWidth:2,pointRadius:0,fill:false}]},
    options:{responsive:true,maintainAspectRatio:false,animation:false,parsing:false,
      plugins:{legend:{display:false}},
      scales:{x:{type:'linear',min:0,title:{display:true,text:I18N.chart_time_title}},
              y:{beginAtZero:true,title:{display:true,text:I18N.chart_price_title}}}}});
  $('#third_eje').on('change',third_refresh);
  $('#third_btnLimpiar').on('click',()=>{$('#third_jsonErr').text('');third_reset();});
  $('#third_btnAplicar').on('click',()=>{
    try{
      const o=JSON.parse($('#third_json').val());
      if(!Array.isArray(o.pasos))throw new Error(I18N.err_missing_pasos);
      $('#third_jsonErr').text(''); third_reset(o.pasos);
    }catch(e){$('#third_jsonErr').text(I18N.err_invalid_json + e.message);}
  });
  third_reset();
});
</script>
</body>
</html>