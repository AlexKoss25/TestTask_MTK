<?php
require __DIR__ . '/../includes/db.php';

// Получаем все запросы с пользователями
$stmt = $pdo->query("
    SELECT q.id, q.title, q.sql_text, u.name AS user_name
    FROM queries q
    JOIN users u ON q.user_id = u.id
    ORDER BY u.name, q.id
");
$queries = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Хранилище SQL-запросов</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body class="p-4">

<div class="container">
    <h1 class="mb-4">📦 Хранилище SQL-запросов</h1>

    <table class="table table-sm table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Пользователь</th>
                <th>Название</th>
                <th>SQL</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($queries as $q): ?>
            <tr>
                <td><?= htmlspecialchars($q['user_name']) ?></td>
                <td><?= htmlspecialchars($q['title']) ?></td>
                <td><code><?= htmlspecialchars($q['sql_text']) ?></code></td>
                <td>
                    <button class="btn btn-sm btn-success btn-execute" data-id="<?= $q['id'] ?>">Выполнить</button>
                    <button class="btn btn-sm btn-primary btn-edit" data-id="<?= $q['id'] ?>">Редактировать</button>
                    <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $q['id'] ?>">Удалить</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <button class="btn btn-sm btn-outline-primary" id="btn-add">➕ Добавить запрос</button>
    <div id="form-container" style="display:none;" class="mt-3"></div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script>
$(function(){

    // ------------------------
    // Добавление нового запроса
    // ------------------------
    $("#btn-add").click(function(){
        $.get("edit.php", function(formHtml){
            $("#form-container").html(formHtml).slideDown();
        });
    });

    // ------------------------
    // Submit формы добавления/редактирования
    // ------------------------
    $("#form-container").on("submit", "form", function(e){
        e.preventDefault();
        let form = $(this);

        $.post("edit.php", form.serialize(), function(response){
            if(response.status === "ok"){
                if(response.action === "insert"){
                    let userName = $("#user_id option:selected").text();
                    let row = `<tr>
                        <td>${userName}</td>
                        <td>${response.title}</td>
                        <td><code>${response.sql_text}</code></td>
                        <td>
                            <button class="btn btn-sm btn-success btn-execute" data-id="${response.id}">Выполнить</button>
                            <button class="btn btn-sm btn-primary btn-edit" data-id="${response.id}">Редактировать</button>
                            <button class="btn btn-sm btn-danger btn-delete" data-id="${response.id}">Удалить</button>
                        </td>
                    </tr>`;
                    $("table tbody").append(row);

                } else if(response.action === "update"){
                    let row = $("button.btn-edit[data-id='"+response.id+"']").closest("tr");
                    let userName = $("#user_id option:selected").text();
                    row.find("td:eq(0)").text(userName);
                    row.find("td:eq(1)").text(response.title);
                    row.find("td:eq(2) code").text(response.sql_text);
                }

                $("#form-container").slideUp().empty();
            } else {
                alert("Ошибка при сохранении");
            }
        }, "json");
    });

    // ------------------------
    // Редактирование запроса
    // ------------------------
    $("table").on("click", ".btn-edit", function(e){
        e.preventDefault();
        let id = $(this).data("id");
        $.get("edit.php?id=" + id, function(formHtml){
            $("#form-container").html(formHtml).slideDown();
        });
    });

    // ------------------------
    // Удаление запроса
    // ------------------------
    $("table").on("click", ".btn-delete", function(){
        if(!confirm("Точно удалить этот запрос?")) return;

        let id = $(this).data("id");
        let row = $(this).closest("tr");

        $.post("delete.php", {id:id}, function(response){
            if(response === "ok"){
                row.remove();
            } else {
                alert("Ошибка при удалении");
            }
        });
    });
    
    // ------------------------
    // Выполнение запроса
    // ------------------------
    $("table").on("click", ".btn-execute", function(){
        let id = $(this).data("id");
        let row = $(this).closest("tr");

        $.get("execute.php?id=" + id, function(response){
            // response должен быть HTML-таблицей с результатами
            // выводим прямо под текущей строкой
            $("#form-container").html(response).slideDown();
            // или можно вставлять прямо после строки
            // row.after('<tr class="query-result"><td colspan="4">'+response+'</td></tr>');
        });
    });
});
</script>

</body>
</html>
