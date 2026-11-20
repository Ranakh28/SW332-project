<?php $pageTitle='Create account'; require 'header.php'; $errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
$name=trim($_POST['name']??''); $email=strtolower(trim($_POST['email']??'')); $password=$_POST['password']??'';
$role=$_POST['role']??'student';
if(!in_array($role,['student','club_leader'],true)) $errors[]='Please choose a valid account type.';
if($name==='') $errors[]='Please enter your name.';
if(!filter_var($email,FILTER_VALIDATE_EMAIL) || !str_ends_with($email,'@'.UNIVERSITY_EMAIL_DOMAIN)) $errors[]='Use your university email ending with @'.UNIVERSITY_EMAIL_DOMAIN.'.';
if(strlen($password)<8) $errors[]='Password must be at least 8 characters.';
if(!$errors){$q=$pdo->prepare('SELECT id FROM users WHERE email=?');$q->execute([$email]);if($q->fetch())$errors[]='This email is already registered.';}
if(!$errors){$q=$pdo->prepare('INSERT INTO users(name,email,password,role) VALUES(?,?,?,?)');$q->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),$role]);flash($role==='club_leader'?'Club leader account created. You can now log in and create your club.':'Student account created. You can now log in.');redirect('login.php');}
}
?>
<div class="auth-wrap"><form class="form-card" method="post"><span class="eyebrow">WELCOME TO THE COMMUNITY</span><h1>Create your account</h1><p class="muted">Use your university email to get started.</p><?php foreach($errors as $err): ?><div class="alert error"><?=e($err)?></div><?php endforeach; ?><?php csrf_field(); ?><label>Full name<input name="name" value="<?=e($_POST['name']??'')?>" required></label><label>University email<input type="email" name="email" value="<?=e($_POST['email']??'')?>" placeholder="name@pnu.edu.sa" required></label><label>Account type<select name="role" required><option value="student" <?=($_POST['role']??'student')==='student'?'selected':''?>>Student</option><option value="club_leader" <?=($_POST['role']??'')==='club_leader'?'selected':''?>>Club Leader</option></select></label><p class="muted">Choose Club Leader if you are creating and managing a student club.</p><label>Password<input type="password" name="password" minlength="8" required></label><button class="btn primary full">Create account</button><p class="form-foot">Already a member? <a href="login.php">Log in</a></p></form></div>
<?php require 'footer.php'; ?>
