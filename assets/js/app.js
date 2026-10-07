function qs(s){return document.querySelector(s)}
function toast(msg,type='success'){
  let el=document.createElement('div'); el.className='toast '+type; el.textContent=msg;
  document.body.appendChild(el); setTimeout(()=>el.classList.add('show'),10); setTimeout(()=>{el.classList.remove('show');setTimeout(()=>el.remove(),300)},2600);
}
function filterTable(id,q){q=q.toLowerCase();document.querySelectorAll('#'+id+' tbody tr').forEach(r=>r.style.display=r.innerText.toLowerCase().includes(q)?'':'none')}
function openModal(){qs('#deviceModal').classList.add('show');qs('#deviceForm').reset();qs('#device_id').value='';qs('#modalTitle').textContent='Add Device'}
function closeModal(){qs('#deviceModal')?.classList.remove('show')}
function editDevice(d){
  openModal(); document.querySelector('#modalTitle').textContent='Edit Device';
  for(const k of ['id','device_name','ip_address','mac_address','device_type','location','bandwidth_limit']){
    const el=qs('#device_'+k); if(el) el.value=d[k]??'';
  }
}
async function api(url,opts={}){const r=await fetch(url,opts);const j=await r.json();if(!j.ok)throw new Error(j.message||'Request failed');return j}
document.addEventListener('DOMContentLoaded',()=>{
  const form=qs('#deviceForm');
  if(form)form.addEventListener('submit',async e=>{
    e.preventDefault();
    try{await api('api/devices.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(Object.fromEntries(new FormData(form)))});
      closeModal();toast('Device saved successfully');setTimeout(()=>location.reload(),500);
    }catch(err){toast(err.message,'danger')}
  });
  const c=qs('#clock'); if(c){const tick=()=>c.textContent=new Date().toLocaleTimeString();tick();setInterval(tick,1000)}
  if(qs('#bandwidthChart')) drawChart();
});
async function deleteDevice(id){if(!confirm('Delete this device?'))return;try{await api('api/devices.php?id='+id,{method:'DELETE'});toast('Device deleted');setTimeout(()=>location.reload(),500)}catch(e){toast(e.message,'danger')}}
async function monitorAll(){try{await api('api/monitor.php');toast('Network status refreshed');setTimeout(()=>location.reload(),500)}catch(e){toast(e.message,'danger')}}
async function refreshDashboard(){await monitorAll()}
async function simulateTraffic(){await monitorAll()}
async function resolveAlert(id){try{await api('api/resolve_alert.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});toast('Alert resolved');setTimeout(()=>location.reload(),400)}catch(e){toast(e.message,'danger')}}
async function generateAlert(){try{await api('api/test_alert.php',{method:'POST'});toast('Test alert created');setTimeout(()=>location.reload(),400)}catch(e){toast(e.message,'danger')}}
async function quickLimit(id,current){const v=prompt('New bandwidth limit (Mbps):',current);if(v===null)return;try{await api('api/limit.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,limit:parseFloat(v)})});toast('Bandwidth limit updated');setTimeout(()=>location.reload(),400)}catch(e){toast(e.message,'danger')}}
function drawChart(){
 const c=qs('#bandwidthChart'),ctx=c.getContext('2d'),w=c.width=c.clientWidth*2,h=c.height=290;c.style.height='145px';ctx.scale(2,2);
 let W=c.clientWidth,H=145, pts=Array.from({length:20},(_,i)=>30+Math.random()*55);
 ctx.clearRect(0,0,W,H);ctx.beginPath();pts.forEach((v,i)=>{let x=i*(W/(pts.length-1)),y=H-v*1.25;i?ctx.lineTo(x,y):ctx.moveTo(x,y)});ctx.strokeStyle=getComputedStyle(document.body).getPropertyValue('--accent')||'#4f7cff';ctx.lineWidth=2;ctx.stroke();
 ctx.lineTo(W,H);ctx.lineTo(0,H);ctx.closePath();ctx.globalAlpha=.08;ctx.fillStyle=ctx.strokeStyle;ctx.fill();ctx.globalAlpha=1;
}
window.addEventListener('resize',()=>{if(qs('#bandwidthChart'))drawChart()});
function toggleTheme(){document.body.classList.toggle('dark');localStorage.setItem('campusDark',document.body.classList.contains('dark')?'1':'0')}
if(localStorage.getItem('campusDark')==='1')document.body.classList.add('dark');
function showDeviceInfo(d){const m=qs('#infoModal');if(!m)return;qs('#infoTitle').textContent=d.device_name;qs('#infoBody').innerHTML='<div class="detail-item"><small>IP</small><b>'+d.ip_address+'</b></div><div class="detail-item"><small>MAC</small><b>'+(d.mac_address||'—')+'</b></div><div class="detail-item"><small>Type</small><b>'+d.device_type+'</b></div><div class="detail-item"><small>Location</small><b>'+(d.location||'—')+'</b></div><div class="detail-item"><small>Status</small><b>'+d.status+'</b></div><div class="detail-item"><small>Traffic</small><b>'+d.download_mbps+' / '+d.bandwidth_limit+' Mbps</b></div>';m.classList.add('show')}
async function runSpeedTest(){const b=document.querySelector('.speed-btn'),o=qs('#speedOrb'),s=qs('#speedStatus');b.disabled=true;s.textContent='Testing connection…';let v=0;const t=setInterval(()=>{v=Math.min(95,v+Math.random()*14);qs('#speedValue').textContent=Math.round(v);o.style.background='conic-gradient(var(--accent) '+v+'%,#e9edf5 0)'},130);await new Promise(r=>setTimeout(r,1700));clearInterval(t);const d=(65+Math.random()*35).toFixed(2),u=(15+Math.random()*20).toFixed(2),p=Math.round(8+Math.random()*22);qs('#speedValue').textContent=d;qs('#downloadResult').textContent=d+' Mbps';qs('#uploadResult').textContent=u+' Mbps';qs('#pingResult').textContent=p+' ms';s.textContent='Test completed';o.style.background='conic-gradient(var(--green) 88%,#e9edf5 0)';b.disabled=false}
async function deleteUser(id){if(!confirm('Delete this user?'))return;try{await api('api/users.php?id='+id,{method:'DELETE'});toast('User deleted');setTimeout(()=>location.reload(),400)}catch(e){toast(e.message,'danger')}}
document.addEventListener('DOMContentLoaded',()=>{const uf=qs('#userForm');if(uf)uf.addEventListener('submit',async e=>{e.preventDefault();try{await api('api/users.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(Object.fromEntries(new FormData(uf)))});toast('User created');setTimeout(()=>location.reload(),400)}catch(x){toast(x.message,'danger')}});const sf=qs('#settingsForm');if(sf)sf.addEventListener('submit',async e=>{e.preventDefault();try{await api('api/settings.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(Object.fromEntries(new FormData(sf)))});toast('Settings saved')}catch(x){toast(x.message,'danger')}});});
async function updateNotifications(){try{const j=await api('api/alerts.php');const n=j.alerts.filter(a=>!a.is_resolved).length;if(qs('#notifCount'))qs('#notifCount').textContent=n}catch(e){}} updateNotifications();setInterval(updateNotifications,10000);
