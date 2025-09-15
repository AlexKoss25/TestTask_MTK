<?php
require __DIR__ . '/../includes/db.php';

$users = $pdo->query("SELECT id, name FROM users ORDER BY name")->fetchAll();

// -------------------------
// Определяем id запроса (для редактирования)
$id = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
} else {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
}

// -------------------------
// Получаем данные запроса для редактирования
$queryData = ['user_id'=>'','title'=>'','sql_text'=>''];
if ($id && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM queries WHERE id=?");
    $stmt->execute([$id]);
    $queryData = $stmt->fetch();
    if (!$queryData) {
        echo "Запрос не найден";
        exit;
    }
}

// -------------------------
// Обработка POST (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $user_id = $_POST['user_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $sql_text = $_POST['sql_text'] ?? '';

    if ($id) {
        // Редактирование существующего запроса
        $stmt = $pdo->prepare("UPDATE queries SET user_id=?, title=?, sql_text=? WHERE id=?");
        $stmt->execute([$user_id, $title, $sql_text, $id]);
        echo json_encode([
            'status'=>'ok',
            'action'=>'update',
            'id'=>$id,
            'user_id'=>$user_id,
            'title'=>$title,
            'sql_text'=>$sql_text
        ]);
    } else {
        // Добавление нового запроса
        $stmt = $pdo->prepare("INSERT INTO queries (user_id, title, sql_text) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $title, $sql_text]);
        $newId = $pdo->lastInsertId();
        echo json_encode([
            'status'=>'ok',
            'action'=>'insert',
            'id'=>$newId,
            'user_id'=>$user_id,
            'title'=>$title,
            'sql_text'=>$sql_text
        ]);
    }
    exit;
}

// -------------------------
// GET → выводим HTML формы для добавления/редактирования
?>
<form class="form">
    <?php if($id): ?>
        <input type="hidden" name="id" value="<?= $id ?>">
    <?php endif; ?>

    <div class="form-group">
        <label for="user_id">Пользователь</label>
        <select name="user_id" id="user_id" class="form-control form-control-sm" required>
            <option value="">-- выберите --</option>
            <?php foreach($users as $u): ?>
                <option value="<?= $u['id'] ?>" <?= $u['id']==$queryData['user_id']?'selected':'' ?>>
                    <?= htmlspecialchars($u['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="title">Название</label>
        <input type="text" name="title" id="title" class="form-control form-control-sm"
               value="<?= htmlspecialchars($queryData['title'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label for="sql_text">SQL-текст</label>
        <textarea name="sql_text" id="sql_text" rows="5" class="form-control form-control-sm" required><?= htmlspecialchars($queryData['sql_text'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-sm btn-primary">💾 Сохранить</button>
    <a href="#" class="btn btn-sm btn-secondary" onclick="$('#form-container').slideUp().empty();return false;">⬅ Отмена</a>
</form>
