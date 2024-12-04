<?php
$host = 'localhost';
$dbname = 'school_management';
$username = 'root';
$password = '';

// подключение к базе
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // добавление студента
    if (isset($_POST['add_student'])) {
        $name = $_POST['name'];
        $group_id = empty($_POST['group_id']) ? NULL : $_POST['group_id'];
        $stmt = $conn->prepare("INSERT INTO students (name, group_id) VALUES (?, ?)");
        $stmt->bind_param("si", $name, $group_id);
        $stmt->execute();
        $message = "Студент добавлен!";
        $stmt->close();
    }
    

    // добавление группы
    if (isset($_POST['add_group'])) {
        $group_name = $_POST['group_name'];
        $stmt = $conn->prepare("INSERT INTO groups (name) VALUES (?)");
        $stmt->bind_param("s", $group_name);
        $stmt->execute();
        $message = "Группа добавлена!";
        $stmt->close();
    }

    // привязка студента к группе
    if (isset($_POST['assign_group'])) {
        $student_id = $_POST['student_id'];
        $group_id = $_POST['group_id'];
        $stmt = $conn->prepare("UPDATE students SET group_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $group_id, $student_id);
        $stmt->execute();
        $message = "Студент привязан к группе!";
        $stmt->close();
    }

    // регистрация студента на курс
    if (isset($_POST['register_course'])) {
        $student_id = $_POST['student_id'];
        $course_id = $_POST['course_id'];
        $stmt = $conn->prepare("INSERT INTO student_courses (student_id, course_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $student_id, $course_id);
        $stmt->execute();
        $message = "Студент зарегистрирован на курс!";
        $stmt->close();
    }

    // удалене студента
    if (isset($_POST['delete_student'])) {
        $student_id = $_POST['student_id'];
        $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $message = "Студент удален!";
        $stmt->close();
    }

    // обновление имени студента
    if (isset($_POST['update_student_name'])) {
        $student_id = $_POST['student_id'];
        $new_name = $_POST['new_name'];
        $stmt = $conn->prepare("UPDATE students SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $new_name, $student_id);
        $stmt->execute();
        $message = "Имя студента обновлено!";
        $stmt->close();
    }

    // добавление нового курса
    if (isset($_POST['add_course'])) {
        $course_name = $_POST['course_name'];
        $teacher_id = $_POST['teacher_id'];
        $stmt = $conn->prepare("INSERT INTO courses (name, teacher_id) VALUES (?, ?)");
        $stmt->bind_param("si", $course_name, $teacher_id);
        $stmt->execute();
        $message = "Курс добавлен!";
        $stmt->close();
    }

    // регистрация нового учителя
    if (isset($_POST['add_teacher'])) {
        $teacher_name = $_POST['teacher_name'];
        $stmt = $conn->prepare("INSERT INTO teachers (name) VALUES (?)");
        $stmt->bind_param("s", $teacher_name);
        $stmt->execute();
        $message = "Преподаватель добавлен!";
        $stmt->close();
    }

    // удаление курса
    if (isset($_POST['delete_course'])) {
        $course_id = $_POST['course_id'];
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $message = "Курс удален!";
        $stmt->close();
    }
}

// получение данных для вывода
$students_result = $conn->query("SELECT students.name AS student_name, groups.name AS group_name FROM students LEFT JOIN groups ON students.group_id = groups.id");
$courses_result = $conn->query("SELECT courses.name AS course_name, COUNT(student_courses.student_id) AS student_count FROM courses LEFT JOIN student_courses ON courses.id = student_courses.course_id GROUP BY courses.name");
$teachers_result = $conn->query("SELECT teachers.name AS teacher_name, courses.name AS course_name FROM teachers LEFT JOIN courses ON teachers.id = courses.teacher_id");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>редачу базу данных</title>
</head>
<body>
    <style>
        * {
            font-family: Helvetica;
        }
    </style>

    <h1>Управление базой данных</h1>

    <h2>Добавить студента</h2>
    <form method="POST">
    <input type="text" name="name" placeholder="Имя студента" required>
    <select name="group_id">
        <option value="">Без группы</option>
        <?php
        $groups = $conn->query("SELECT id, name FROM groups");
        while ($group = $groups->fetch_assoc()):
        ?>
            <option value="<?= $group['id'] ?>"><?= $group['name'] ?></option>
        <?php endwhile; ?>
    </select>
    <button type="submit" name="add_student">Добавить</button>
</form>

    <h2>Добавить группу</h2>
    <form method="POST">
        <input type="text" name="group_name" placeholder="Название группы" required>
        <button type="submit" name="add_group">Добавить</button>
    </form>

    <h2>Список студентов с их группами</h2>
    <table border="1">
        <tr>
            <th>Имя студента</th>
            <th>Название группы</th>
        </tr>
        <?php while ($row = $students_result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['student_name'] ?></td>
                <td><?= $row['group_name'] ?: 'Нет группы' ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Курсы и количество студентов</h2>
    <table border="1">
        <tr>
            <th>Название курса</th>
            <th>Количество студентов</th>
        </tr>
        <?php while ($row = $courses_result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['course_name'] ?></td>
                <td><?= $row['student_count'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Преподаватели и их курсы</h2>
    <table border="1">
        <tr>
            <th>Имя преподавателя</th>
            <th>Название курса</th>
        </tr>
        <?php while ($row = $teachers_result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['teacher_name'] ?></td>
                <td><?= $row['course_name'] ?: 'Нет курса' ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>