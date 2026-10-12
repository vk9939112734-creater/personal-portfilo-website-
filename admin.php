<?php
require __DIR__ . '/config.php';
require_login();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'save_content') {
            $allowed = ['logo_name','kicker','name','role','hero_description','about','profile_image','resume_url','email','whatsapp','github_url','linkedin_url','instagram_url','footer_text'];
            $stmt = db()->prepare('INSERT INTO site_content (content_key, content_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)');
            foreach ($allowed as $key) {
                $value = trim((string)($_POST[$key] ?? ''));
                if (in_array($key, ['profile_image','resume_url','github_url','linkedin_url','instagram_url'], true) && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    throw new RuntimeException("Please use a complete https:// URL for $key.");
                }
                if (in_array($key, ['profile_image','resume_url','github_url','linkedin_url','instagram_url'], true) && $value !== '' && !preg_match('~^https://~i', $value)) {
                    throw new RuntimeException("Only HTTPS URLs are allowed for $key.");
                }
                $stmt->execute([$key, $value]);
            }
            $message = 'Site content saved.';
        } elseif ($action === 'add_skill') {
            $name = trim($_POST['skill_name'] ?? '');
            $pct = filter_var($_POST['percentage'] ?? 0, FILTER_VALIDATE_INT, ['options'=>['min_range'=>0,'max_range'=>100]]);
            if ($name === '' || $pct === false) throw new RuntimeException('Skill name and percentage (0–100) are required.');
            $stmt = db()->prepare('INSERT INTO skills (name, percentage, description, sort_order) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $pct, trim($_POST['skill_description'] ?? ''), (int)db()->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM skills')->fetchColumn()]);
            $message = 'Skill added.';
        } elseif ($action === 'save_skill') {
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            $name = trim($_POST['name'] ?? '');
            $pct = filter_var($_POST['percentage'] ?? 0, FILTER_VALIDATE_INT, ['options'=>['min_range'=>0,'max_range'=>100]]);
            if (!$id || $name === '' || $pct === false) throw new RuntimeException('Invalid skill data.');
            db()->prepare('UPDATE skills SET name=?, percentage=?, description=? WHERE id=?')->execute([$name,$pct,trim($_POST['description'] ?? ''),$id]);
            $message = 'Skill updated.';
        } elseif ($action === 'delete_skill') {
            db()->prepare('DELETE FROM skills WHERE id=?')->execute([(int)($_POST['id'] ?? 0)]);
            $message = 'Skill deleted.';
        } elseif ($action === 'add_project') {
            $title = trim($_POST['title'] ?? '');
            if ($title === '') throw new RuntimeException('Project title is required.');
            $stmt = db()->prepare('INSERT INTO projects (title, description, category, image_url, live_url, code_url, sort_order) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$title,trim($_POST['description'] ?? ''),trim($_POST['category'] ?? 'Web'),url_value($_POST['image_url'] ?? ''),url_value($_POST['live_url'] ?? ''),url_value($_POST['code_url'] ?? ''),(int)db()->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM projects')->fetchColumn()]);
            $message = 'Project added.';
        } elseif ($action === 'save_project') {
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            $title = trim($_POST['title'] ?? '');
            if (!$id || $title === '') throw new RuntimeException('Invalid project data.');
            db()->prepare('UPDATE projects SET title=?, description=?, category=?, image_url=?, live_url=?, code_url=? WHERE id=?')->execute([$title,trim($_POST['description'] ?? ''),trim($_POST['category'] ?? 'Web'),url_value($_POST['image_url'] ?? ''),url_value($_POST['live_url'] ?? ''),url_value($_POST['code_url'] ?? ''),$id]);
            $message = 'Project updated.';
        } elseif ($action === 'delete_project') {
            db()->prepare('DELETE FROM projects WHERE id=?')->execute([(int)($_POST['id'] ?? 0)]);
            $message = 'Project deleted.';
        }
    } catch (Throwable $ex) {
        $message = $ex instanceof RuntimeException ? $ex->getMessage() : 'Save failed. Check database connection and server logs.';
    }
}
function url_value(string $v): string {
    $v = trim($v);
    if ($v === '') return '';
    if (!filter_var($v, FILTER_VALIDATE_URL) || !preg_match('~^https://~i', $v)) throw new RuntimeException('Project links and image URLs must be valid HTTPS URLs.');
    return $v;
}
$content = [];
foreach (db()->query('SELECT content_key, content_value FROM site_content') as $row) $content[$row['content_key']] = $row['content_value'];
$skills = db()->query('SELECT * FROM skills ORDER BY sort_order, id')->fetchAll();
$projects = db()->query('SELECT * FROM projects ORDER BY sort_order, id')->fetchAll();
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Portfolio Admin Dashboard</title><link rel="stylesheet" href="admin.css"></head><body>
<header class="topbar"><div><strong>Portfolio Admin</strong><small><?=e($_SESSION['admin_email'] ?? '')?></small></div><nav><a href="api.php" target="_blank">View public JSON</a><a href="logout.php">Logout</a></nav></header>
<main class="dashboard">
<h1>Manage your portfolio</h1><p class="muted">Edit content below, then press Save. This dashboard runs locally on XAMPP.</p>
<?php if ($message): ?><div class="notice"><?=e($message)?></div><?php endif; ?>
<section class="panel"><h2>Profile, About, links and resume</h2>
<form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save_content">
<div class="form-grid">
<?php $labels=['logo_name'=>'Navbar logo','kicker'=>'Availability line','name'=>'Name','role'=>'Main headline / role','hero_description'=>'Hero description','about'=>'About section','profile_image'=>'Profile image HTTPS URL','resume_url'=>'Resume PDF HTTPS URL','email'=>'Contact email','whatsapp'=>'WhatsApp number (country code, digits only)','github_url'=>'GitHub URL','linkedin_url'=>'LinkedIn URL','instagram_url'=>'Instagram URL','footer_text'=>'Footer text']; foreach($labels as $key=>$label): ?>
<label class="<?=in_array($key,['hero_description','about'],true)?'full':''?>"><?=e($label)?>
<?php if (in_array($key,['hero_description','about'],true)): ?><textarea name="<?=e($key)?>" rows="4"><?=e($content[$key] ?? '')?></textarea><?php else: ?><input name="<?=e($key)?>" value="<?=e($content[$key] ?? '')?>" <?=in_array($key,['email'],true)?'type="email"':'type="text"'?>><?php endif; ?>
</label>
<?php endforeach; ?></div><button>Save profile and settings</button></form></section>

<section class="panel"><h2>Skills</h2><div class="records">
<?php foreach($skills as $s): ?><form class="record" method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save_skill"><input type="hidden" name="id" value="<?=(int)$s['id']?>">
<label>Skill<input name="name" required value="<?=e($s['name'])?>"></label><label>Percent (0–100)<input type="number" min="0" max="100" name="percentage" required value="<?=(int)$s['percentage']?>"></label><label>Description<input name="description" value="<?=e($s['description'])?>"></label><button>Update</button></form>
<form method="post" class="inline-delete" onsubmit="return confirm('Delete this skill?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete_skill"><input type="hidden" name="id" value="<?=(int)$s['id']?>"><button class="danger">Delete</button></form><?php endforeach; ?></div>
<h3>Add a skill</h3><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="add_skill"><label>Name<input name="skill_name" required></label><label>Percentage<input type="number" min="0" max="100" name="percentage" required value="70"></label><label class="full">Description<input name="skill_description"></label><button>Add skill</button></form></section>

<section class="panel"><h2>Projects</h2><div class="records">
<?php foreach($projects as $p): ?><form method="post" class="project-record"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save_project"><input type="hidden" name="id" value="<?=(int)$p['id']?>">
<label>Title<input name="title" required value="<?=e($p['title'])?>"></label><label>Category<input name="category" value="<?=e($p['category'])?>"></label><label class="full">Description<textarea name="description" rows="3"><?=e($p['description'])?></textarea></label><label class="full">Image HTTPS URL<input name="image_url" value="<?=e($p['image_url'])?>"></label><label>Live URL<input name="live_url" value="<?=e($p['live_url'])?>"></label><label>Code URL<input name="code_url" value="<?=e($p['code_url'])?>"></label><button>Update project</button></form>
<form method="post" class="inline-delete" onsubmit="return confirm('Delete this project?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete_project"><input type="hidden" name="id" value="<?=(int)$p['id']?>"><button class="danger">Delete</button></form><?php endforeach; ?></div>
<h3>Add a project</h3><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="add_project"><label>Title<input name="title" required></label><label>Category<input name="category" value="Web"></label><label class="full">Description<textarea name="description" rows="3"></textarea></label><label class="full">Image HTTPS URL<input name="image_url"></label><label>Live URL<input name="live_url"></label><label>Code URL<input name="code_url"></label><button>Add project</button></form></section>
<section class="panel"><h2>Important</h2><ul><li>This PHP/MySQL admin panel works only on XAMPP or PHP-supported hosting.</li><li>Changes saved here do not automatically update the GitHub Pages copy. Your public site must fetch the JSON from this PHP backend, and the backend must be hosted online for the live site.</li><li>Image and resume fields accept HTTPS URLs; upload files to your website or a suitable file host and paste the URL.</li><li>Never expose the MySQL password in HTML/JavaScript.</li></ul></section>
</main></body></html>
