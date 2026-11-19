<?php
require __DIR__.'/config.php';require __DIR__.'/lib.php';
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS'])]);session_start();
header('Content-Type: application/json; charset=utf-8');
function out($a=[]){echo json_encode($a+['ok'=>1],JSON_UNESCAPED_UNICODE);exit;}
function fail($m,$c=400){http_response_code($c);echo json_encode(['ok'=>0,'err'=>$m],JSON_UNESCAPED_UNICODE);exit;}
function need($r){$u=$_SESSION['u']??null;if(!$u||!in_array($u['role'],$r))fail('Akses ditolak. Silakan masuk kembali.',403);return $u;}
function q($sql,$p=[]){$s=db()->prepare($sql);$s->execute($p);return $s;}
function now_(){return date('Y-m-d H:i:s');}
function reports($w,$p,$name=''){$R=q("SELECT id,area_id,deskripsi,status,created,lat,lng,foto FROM reports $w ORDER BY id",$p)->fetchAll();if(!$R)return[];
$ids=implode(',',array_map(fn($r)=>(int)$r['id'],$R));$h=[];foreach(db()->query("SELECT report_id,status,waktu FROM history WHERE report_id IN($ids) ORDER BY id") as $x)$h[$x['report_id']][]=[$x['status'],substr($x['waktu'],0,16)];
return array_map(fn($r)=>['id'=>(int)$r['id'],'own'=>$name,'hp'=>'','ar'=>$r['area_id']-1,'d'=>$r['deskripsi'],'st'=>$r['status'],'t'=>substr($r['created'],0,16),'lat'=>$r['lat'],'lng'=>$r['lng'],'img'=>$r['foto']?'api.php?a=foto&id='.$r['id']:'','h'=>$h[$r['id']]??[]],$R);}
$in=json_decode(file_get_contents('php://input'),true)?:[];$a=$_GET['a']??'';
try{
if($a=='me')out(['u'=>$_SESSION['u']??null]);
if($a=='logout'){session_destroy();out();}
if($a=='login'){usleep(300000);$u=q('SELECT * FROM users WHERE hp_hash=?',[hh($in['hp']??'')])->fetch();
 if(!$u||!password_verify($in['pw']??'',$u['pass_hash']))fail('Nomor HP atau kata sandi salah.',401);
 if($u['role']!=($in['role']??''))fail('Peran tidak sesuai dengan akun ini.',403);
 if($u['status']=='diblokir')fail('Akun diblokir. Hubungi admin.',403);
 if($u['status']=='menunggu')fail('Akun belum diverifikasi OTP.',403);
 if($u['status']!='aktif')fail('Akun dinonaktifkan.',403);
 session_regenerate_id(true);$_SESSION['u']=['id'=>(int)$u['id'],'role'=>$u['role'],'name'=>dec($u['nama_enc'])];out(['u'=>$_SESSION['u']]);}
if($a=='daftar'){$n=trim($in['nama']??'');$h=hpn($in['hp']??'');$p=$in['pw']??'';
 if($n===''||strlen($h)<9||strlen($p)<6)fail('Lengkapi nama, nomor HP valid, dan kata sandi minimal 6 karakter.');
 $k=hh($h);$x=q('SELECT status FROM users WHERE hp_hash=?',[$k])->fetch();
 if($x&&$x['status']!='menunggu')fail('Nomor HP sudah terdaftar. Silakan masuk.');
 if($x)q('DELETE FROM users WHERE hp_hash=?',[$k]);
 q("INSERT INTO users(role,nama_enc,hp_enc,hp_hash,pass_hash,area_id,status,created) VALUES('warga',?,?,?,?,?,'menunggu',?)",[enc($n),enc($h),$k,password_hash($p,PASSWORD_DEFAULT),max(1,min(3,(int)($in['area']??0)+1)),now_()]);
 $o=sprintf('%06d',random_int(0,999999));q('REPLACE INTO otp VALUES(?,?,?)',[$k,$o,date('Y-m-d H:i:s',time()+300)]);
 out(DEMO_OTP?['otp'=>$o]:[]);}
if($a=='verif'){$k=hh($in['hp']??'');if(!q('SELECT 1 FROM otp WHERE hp_hash=? AND kode=? AND exp>?',[$k,$in['kode']??'',now_()])->fetch())fail('Kode OTP salah atau kedaluwarsa.');
 q("UPDATE users SET status='aktif' WHERE hp_hash=? AND status='menunggu'",[$k]);q('DELETE FROM otp WHERE hp_hash=?',[$k]);out();}
if($a=='data'){$u=need(['warga','pengurus','admin']);$d=['R'=>[],'N'=>[],'log'=>[],'acc'=>[]];
 if($u['role']=='warga'){$d['R']=reports('WHERE user_id=?',[$u['id']],$u['name']);
  foreach(q('SELECT pesan,waktu FROM notifications WHERE user_id=? ORDER BY id DESC',[$u['id']]) as $n)$d['N'][]=['own'=>$u['name'],'m'=>$n['pesan'],'t'=>substr($n['waktu'],0,16)];}
 else $d['R']=reports('',[]);
 if($u['role']=='admin'){foreach(q('SELECT a.waktu,a.report_id,a.alasan,u.nama_enc FROM audit a JOIN users u ON u.id=a.admin_id ORDER BY a.id DESC LIMIT 200') as $l)$d['log'][]=[substr($l['waktu'],0,16),dec($l['nama_enc']),(int)$l['report_id'],$l['alasan']];
  foreach(q("SELECT id,nama_enc,status FROM users WHERE role='pengurus'") as $p)$d['acc'][]=[dec($p['nama_enc']),$p['status']=='aktif'?1:0,(int)$p['id']];}
 out($d);}
if($a=='kirim'){$u=need(['warga']);$d=trim($_POST['d']??'');$lat=(float)($_POST['lat']??0);$lng=(float)($_POST['lng']??0);$ar=max(1,min(3,(int)($_POST['ar']??0)+1));
 if(mb_strlen($d)<10)fail('Deskripsi minimal 10 karakter.');if(!$lat&&!$lng)fail('Lokasi belum diisi.');
 if(q('SELECT COUNT(*) FROM reports WHERE user_id=? AND created>=?',[$u['id'],date('Y-m-d 00:00:00')])->fetchColumn()>=MAX_LAPORAN)fail('Batas laporan harian tercapai (maks. '.MAX_LAPORAN.' per hari).',429);
 $f=$_FILES['foto']??null;if(!$f||$f['error']!==0)fail('Lampirkan minimal satu foto (maks. 5 MB).');if($f['size']>5242880)fail('Foto melebihi 5 MB.',422);
 $i=@getimagesize($f['tmp_name']);$ext=[IMAGETYPE_JPEG=>'jpg',IMAGETYPE_PNG=>'png',IMAGETYPE_WEBP=>'webp'][$i[2]??0]??null;if(!$ext)fail('Format foto tidak didukung (JPG/PNG/WEBP).',422);
 $fn=bin2hex(random_bytes(12)).'.'.$ext;if(!move_uploaded_file($f['tmp_name'],__DIR__.'/storage/'.$fn))fail('Gagal menyimpan foto.',500);
 q('INSERT INTO reports(user_id,area_id,deskripsi,foto,lat,lng,status,created) VALUES(?,?,?,?,?,?,?,?)',[$u['id'],$ar,$d,$fn,$lat,$lng,'Baru',now_()]);$id=(int)db()->lastInsertId();
 $t='SGP-'.date('Ymd').'-'.str_pad($id,4,'0',STR_PAD_LEFT);q('UPDATE reports SET tiket=?,label=? WHERE id=?',[$t,'Warga #'.$id,$id]);
 q('INSERT INTO history(report_id,status,waktu) VALUES(?,?,?)',[$id,'Baru',now_()]);out(['tiket'=>$t]);}
if($a=='foto'){$u=need(['warga','pengurus','admin']);$r=q('SELECT foto,user_id FROM reports WHERE id=?',[(int)($_GET['id']??0)])->fetch();
 if(!$r||!$r['foto']||($u['role']=='warga'&&$r['user_id']!=$u['id']))fail('Tidak ditemukan.',404);
 $m=['jpg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp'][pathinfo($r['foto'],PATHINFO_EXTENSION)]??'application/octet-stream';
 header('Content-Type: '.$m);header('Cache-Control: private, max-age=3600');readfile(__DIR__.'/storage/'.basename($r['foto']));exit;}
if($a=='cari'){$u=need(['warga']);$r=q('SELECT id,deskripsi,status FROM reports WHERE tiket=? AND user_id=?',[strtoupper(trim($in['kode']??'')),$u['id']])->fetch();
 if(!$r)fail('Laporan tidak ditemukan atau bukan milik akun Anda.',404);$h=reports('WHERE id=?',[$r['id']])[0]['h'];out(['r'=>['d'=>$r['deskripsi'],'st'=>$r['status'],'h'=>$h]]);}
if($a=='ubah'){$u=need(['pengurus']);$o=['Baru','Diproses','Selesai'];$id=(int)($in['id']??0);$st=$in['st']??'';$r=q('SELECT status,user_id,tiket FROM reports WHERE id=?',[$id])->fetch();
 if(!$r)fail('Laporan tidak ditemukan.',404);
 if(array_search($st,$o,true)===false||array_search($st,$o,true)!==array_search($r['status'],$o,true)+1)fail("Transisi tidak valid: {$r['status']} → $st.",422);
 q('UPDATE reports SET status=? WHERE id=?',[$st,$id]);q('INSERT INTO history(report_id,status,waktu) VALUES(?,?,?)',[$id,$st,now_()]);
 q('INSERT INTO notifications(user_id,report_id,pesan,waktu) VALUES(?,?,?,?)',[$r['user_id'],$id,"Laporan Anda ({$r['tiket']}) kini berstatus $st.",now_()]);out();}
if($a=='akses'){$u=need(['admin']);$al=trim($in['alasan']??'');if($al==='')fail('Akses ditolak: alasan wajib diisi.',422);
 $r=q('SELECT r.id,r.area_id,u.nama_enc,u.hp_enc FROM reports r JOIN users u ON u.id=r.user_id WHERE r.id=?',[(int)($in['id']??0)])->fetch();if(!$r)fail('Laporan tidak ditemukan.',404);
 q('INSERT INTO audit(admin_id,report_id,alasan,waktu) VALUES(?,?,?,?)',[$u['id'],$r['id'],$al,now_()]);out(['own'=>dec($r['nama_enc']),'hp'=>dec($r['hp_enc']),'ar'=>$r['area_id']-1]);}
if($a=='akun'){need(['admin']);q("UPDATE users SET status=IF(status='aktif','nonaktif','aktif') WHERE id=? AND role='pengurus'",[(int)($in['id']??0)]);out();}
if($a=='csv'){need(['pengurus']);header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="rekap-sigap-'.date('Ymd').'.csv"');
 $o=fopen('php://output','w');fputcsv($o,['area','baru','diproses','selesai']);$n=['RT 01/RW 05','RT 02/RW 05','RT 03/RW 05'];
 foreach($n as $i=>$nm){$row=[$nm];foreach(['Baru','Diproses','Selesai'] as $s)$row[]=q('SELECT COUNT(*) FROM reports WHERE area_id=? AND status=?',[$i+1,$s])->fetchColumn();fputcsv($o,$row);}exit;}
fail('Aksi tidak dikenal.',404);
}catch(Throwable $e){fail('Terjadi kesalahan server. Periksa konfigurasi database.',500);}
