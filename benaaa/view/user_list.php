<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controller/traitement.php';
$cnx = getConnection();
$users = (isset($_GET['search']) && $_GET['search'] !== '') ? searchUsers($cnx, $_GET['search']) : getAllUsers($cnx);
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>User List</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="user_list.css"></head><body>
<div class="container"><h2>User List</h2>
<form method="GET"><input type="text" name="search" placeholder="Search by name..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
<button type="submit">Search</button></form>
<a href="signup.php">Add New User</a>
<table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr></thead><tbody>
<?php if (empty($users)): ?><tr><td colspan="4">No users found.</td></tr>
<?php else: foreach ($users as $user): ?>
<tr><td><?= htmlspecialchars($user->id) ?></td><td><?= htmlspecialchars($user->nom) ?></td>
<td><?= htmlspecialchars($user->email) ?></td>
<td><a href="../controller/update_user.php?id=<?= $user->id ?>">Edit</a>
<a href="../controller/delete_user.php?id=<?= $user->id ?>" onclick="return confirm('Delete?')">Delete</a></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div></body></html>
