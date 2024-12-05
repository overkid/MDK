<?php
$host = 'localhost';
$dbname = 'school_management';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Соединение с базой данных успешно.<br>";
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_student':
            $name = $_POST['name'] ?? '';
            $stmt = $pdo->prepare("INSERT INTO students (name) VALUES (:name)");
            $stmt->execute(['name' => $name]);
            echo "Студент добавлен!<br>";
            break;

        case 'add_group':
            $name = $_POST['name'] ?? '';
            $stmt = $pdo->prepare("INSERT INTO groups (name) VALUES (:name)");
            $stmt->execute(['name' => $name]);
            echo "Группа добавлена!<br>";
            break;

        case 'assign_group':
            $student_id = $_POST['student_id'] ?? '';
            $group_id = $_POST['group_id'] ?? '';
            $stmt = $pdo->prepare("UPDATE students SET group_id = :group_id WHERE id = :student_id");
            $stmt->execute(['student_id' => $student_id, 'group_id' => $group_id]);
            echo "Студент привязан к группе!<br>";
            break;

        case 'add_course':
            $name = $_POST['name'] ?? '';
            $teacher_id = $_POST['teacher_id'] ?? '';
            $stmt = $pdo->prepare("INSERT INTO courses (name, teacher_id) VALUES (:name, :teacher_id)");
            $stmt->execute(['name' => $name, 'teacher_id' => $teacher_id]);
            echo "Курс добавлен!<br>";
            break;
            
    }
}

function showStudents($pdo) {
    $students = $pdo->query("SELECT * FROM students")->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Список студентов</h3>";
    echo "<table border='1'><tr><th>ID</th><th>Имя</th><th>ID группы</th></tr>";
    foreach ($students as $student) {
        echo "<tr><td>{$student['id']}</td><td>{$student['name']}</td><td>{$student['group_id']}</td></tr>";
    }
    echo "</table>";
}

function showGroups($pdo) {
    $groups = $pdo->query("SELECT * FROM groups")->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Список групп</h3>";
    echo "<table border='1'><tr><th>ID</th><th>Название</th></tr>";
    foreach ($groups as $group) {
        echo "<tr><td>{$group['id']}</td><td>{$group['name']}</td></tr>";
    }
    echo "</table>";
}

function showStudentsWithGroups($pdo) {
    $sql = "SELECT students.name AS student_name, groups.name AS group_name 
            FROM students 
            LEFT JOIN groups ON students.group_id = groups.id";
    $result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Студенты с их группами</h3>";
    echo "<table border='1'><tr><th>Студент</th><th>Группа</th></tr>";
    foreach ($result as $row) {
        echo "<tr><td>{$row['student_name']}</td><td>{$row['group_name']}</td></tr>";
    }
    echo "</table>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {

        case 'register_course':
            $student_id = $_POST['student_id'] ?? '';
            $course_id = $_POST['course_id'] ?? '';
            $stmt = $pdo->prepare("INSERT INTO student_courses (student_id, course_id) VALUES (:student_id, :course_id)");
            $stmt->execute(['student_id' => $student_id, 'course_id' => $course_id]);
            echo "Студент зарегистрирован на курс!<br>";
            break;

        case 'add_course':
            $name = $_POST['name'] ?? '';
            $teacher_id = $_POST['teacher_id'] ?? '';
            $stmt = $pdo->prepare("INSERT INTO courses (name, teacher_id) VALUES (:name, :teacher_id)");
            $stmt->execute(['name' => $name, 'teacher_id' => $teacher_id]);
            echo "Курс добавлен!<br>";
            break;

        case 'add_teacher':
            $name = $_POST['name'] ?? '';
            if ($name) {
                $stmt = $pdo->prepare("INSERT INTO teachers (name) VALUES (:name)");
                $stmt->execute(['name' => $name]);
                echo "Преподаватель добавлен!<br>";
            } else {
                echo "Ошибка: Имя преподавателя не может быть пустым.<br>";
                }
            break;
        
        case 'delete_student':
            $student_id = $_POST['student_id'] ?? '';
            if ($student_id) {
                $stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
                $stmt->execute(['id' => $student_id]);
                $student = $stmt->fetch();

                if ($student) {
                    $stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
                    $stmt->execute(['id' => $student_id]);
                    echo "Студент удален!<br>";
                } else {
                    echo "Ошибка: Студент с таким ID не найден.<br>";
                }
            } else {
                echo "Ошибка: ID студента не может быть пустым.<br>";
            }
            break;
        
            case 'update_student_name':
                $student_id = $_POST['student_id'] ?? '';
                $new_name = $_POST['new_name'] ?? '';
            
                if ($student_id && $new_name) {
                    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
                    $stmt->execute(['id' => $student_id]);
                    $student = $stmt->fetch();
            
                    if ($student) {
                        $stmt = $pdo->prepare("UPDATE students SET name = :name WHERE id = :id");
                        $stmt->execute(['name' => $new_name, 'id' => $student_id]);
                        echo "Имя студента обновлено!<br>";
                    } else {
                        echo "Ошибка: Студент с таким ID не найден.<br>";
                    }
                } else {
                    echo "Ошибка: ID студента и новое имя не могут быть пустыми.<br>";
                }
                break;
            
            case 'search_student_by_name':
                $student_name = $_POST['student_name'] ?? '';
                
                if ($student_name) {
                    $stmt = $pdo->prepare("SELECT students.name AS student_name, groups.name AS group_name 
                                            FROM students 
                                            LEFT JOIN groups ON students.group_id = groups.id 
                                            WHERE students.name LIKE :name");
                    $stmt->execute(['name' => "%" . $student_name . "%"]);
                    $students = $stmt->fetchAll();
                
                    if ($students) {
                         echo "<h3>Результаты поиска:</h3>";
                        echo "<table border='1'>
                                <tr>
                                    <th>Имя студента</th>
                                    <th>Группа</th>
                                </tr>";
                        foreach ($students as $student) {
                            echo "<tr>
                                    <td>{$student['student_name']}</td>
                                    <td>" . ($student['group_name'] ? $student['group_name'] : 'Без группы') . "</td>
                                    </tr>";
                        }
                        echo "</table>";
                        } else {
                            echo "Студент с таким именем не найден.<br>";
                        }
                    } else {
                        echo "Ошибка: Имя студента не может быть пустым.<br>";
                    }
                    break;
                
                    case 'search_course_by_name':
                        $course_name = $_POST['course_name'] ?? '';
                    
                        if ($course_name) {
                            $stmt = $pdo->prepare("SELECT courses.id AS course_id, courses.name AS course_name, students.name AS student_name
                                                   FROM courses
                                                   LEFT JOIN student_courses ON courses.id = student_courses.course_id
                                                   LEFT JOIN students ON student_courses.student_id = students.id
                                                   WHERE courses.name LIKE :course_name");
                            $stmt->execute(['course_name' => "%" . $course_name . "%"]);
                            $students_in_course = $stmt->fetchAll();
                    
                            if ($students_in_course) {
                                echo "<h3>Студенты, зарегистрированные на курс:</h3>";
                                echo "<table border='1'>
                                        <tr>
                                            <th>Курс</th>
                                            <th>Студенты</th>
                                        </tr>";
                                foreach ($students_in_course as $row) {
                                    echo "<tr>
                                            <td>{$row['course_name']}</td>
                                            <td>" . ($row['student_name'] ? $row['student_name'] : 'Нет студентов') . "</td>
                                          </tr>";
                                }
                                echo "</table>";
                            } else {
                                echo "Курс с таким названием не найден или на курс не зарегистрированы студенты.<br>";
                            }
                        } else {
                            echo "Ошибка: Название курса не может быть пустым.<br>";
                        }
                        break;

                            case 'delete_course':
                                $course_id = $_POST['course_id'] ?? '';
                            
                                if ($course_id) {
                                    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = :course_id");
                                    $stmt->execute(['course_id' => $course_id]);
                                    $course = $stmt->fetch();
                            
                                    if ($course) {
                                        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = :course_id");
                                        $stmt->execute(['course_id' => $course_id]);
                                        echo "Курс успешно удален вместе с его регистрациями.<br>";
                                    } else {
                                        echo "Ошибка: Курс с таким ID не найден.<br>";
                                    }
                                } else {
                                    echo "Ошибка: Выберите курс для удаления.<br>";
                                }
                                break;
                            
        }
}

function showCoursesWithStudentCount($pdo) {
    $sql = "SELECT courses.name AS course_name, COUNT(student_courses.student_id) AS student_count 
            FROM courses 
            LEFT JOIN student_courses ON courses.id = student_courses.course_id 
            GROUP BY courses.name";
    $result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Курсы и количество студентов</h3>";
    echo "<table border='1'><tr><th>Курс</th><th>Количество студентов</th></tr>";
    foreach ($result as $row) {
        echo "<tr><td>{$row['course_name']}</td><td>{$row['student_count']}</td></tr>";
    }
    echo "</table>";
}

function showTeachersWithCourses($pdo) {
    $sql = "SELECT teachers.name AS teacher_name, courses.name AS course_name 
            FROM teachers 
            LEFT JOIN courses ON teachers.id = courses.teacher_id";
    $result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Преподаватели и их курсы</h3>";
    echo "<table border='1'><tr><th>Преподаватель</th><th>Курс</th></tr>";
    foreach ($result as $row) {
        echo "<tr><td>{$row['teacher_name']}</td><td>{$row['course_name']}</td></tr>";
    }
    echo "</table>";
}

function showStudentsWithoutGroups($pdo) {
    $sql = "SELECT * FROM students WHERE group_id IS NULL";
    $result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Студенты без группы</h3>";
    echo "<table border='1'><tr><th>ID</th><th>Имя</th></tr>";
    foreach ($result as $row) {
        echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td></tr>";
    }
    echo "</table>";
}

function showTeachersWithStudentCount($pdo) {
    $sql = "SELECT teachers.name AS teacher_name, COUNT(student_courses.student_id) AS total_students 
            FROM teachers 
            JOIN courses ON teachers.id = courses.teacher_id 
            JOIN student_courses ON courses.id = student_courses.course_id 
            GROUP BY teachers.id";
    $result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Преподаватели и количество их студентов</h3>";
    echo "<table border='1'><tr><th>Преподаватель</th><th>Количество студентов</th></tr>";
    foreach ($result as $row) {
        echo "<tr><td>{$row['teacher_name']}</td><td>{$row['total_students']}</td></tr>";
    }
    echo "</table>";
}

function getOptions($pdo, $table, $id_field, $name_field) {
    $stmt = $pdo->query("SELECT $id_field, $name_field FROM $table");
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $options;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'filter_students_by_group') {
    $group_id = $_POST['group_id'] ?? '';

    if ($group_id) {
        $stmt = $pdo->prepare("SELECT students.name AS student_name, groups.name AS group_name 
                               FROM students 
                               LEFT JOIN groups ON students.group_id = groups.id
                               WHERE students.group_id = :group_id");
        $stmt->execute(['group_id' => $group_id]);
        $students = $stmt->fetchAll();

        if ($students) {
            echo "<h3>Студенты из группы: {$_POST['group_id']}</h3>";
            echo "<table border='1'>
                    <tr>
                        <th>Имя студента</th>
                        <th>Группа</th>
                    </tr>";

            foreach ($students as $student) {
                echo "<tr>
                        <td>{$student['student_name']}</td>
                        <td>{$student['group_name']}</td>
                      </tr>";
            }

            echo "</table>";
        } else {
            echo "Нет студентов в выбранной группе.";
        }
    } else {
        echo "Пожалуйста, выберите группу.";
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'filter_students_multiple_courses') {
    $sql = "SELECT students.name AS student_name, COUNT(student_courses.course_id) AS course_count 
            FROM students 
            JOIN student_courses ON students.id = student_courses.student_id 
            GROUP BY students.id 
            HAVING course_count > 1";
    $stmt = $pdo->query($sql);
    $students = $stmt->fetchAll();

    if ($students) {
        echo "<h3>Студенты, зарегистрированные на несколько курсов:</h3>";
        echo "<table border='1'>
                <tr>
                    <th>Имя студента</th>
                    <th>Количество курсов</th>
                </tr>";

        foreach ($students as $student) {
            echo "<tr>
                    <td>{$student['student_name']}</td>
                    <td>{$student['course_count']}</td>
                  </tr>";
        }

        echo "</table>";
    } else {
        echo "Нет студентов, зарегистрированных на несколько курсов.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление школой</title>
</head>

<body>

<style>
    * {
        font-family: Helvetica;
    }
</style>

    <h2>Поиск студента по имени</h2>
    <form method="POST">
        <input type="hidden" name="action" value="search_student_by_name">
        <label for="student_name">Имя студента:</label>
        <input type="text" id="student_name" name="student_name" required>
        <button type="submit">Найти студента</button>
    </form>

    <h2>Поиск курса по названию</h2>
    <form method="POST">
        <input type="hidden" name="action" value="search_course_by_name">
        <label for="course_name">Название курса:</label>
        <input type="text" id="course_name" name="course_name" required>
        <button type="submit">Найти курс</button>
    </form>

    <h2>Добавить студента</h2>
    <form method="POST">
        <input type="hidden" name="action" value="add_student">
        <input type="text" name="name" placeholder="Имя студента" required>
        <button type="submit">Добавить</button>
    </form>

    <h2>Удалить студента</h2>
    <form method="POST">
        <input type="hidden" name="action" value="delete_student">
        <label for="student_id">ID студента:</label>
        <input type="number" id="student_id" name="student_id" required>
        <button type="submit">Удалить студента</button>
    </form>

    <h2>Обновить имя студента</h2>
    <form method="POST">
        <input type="hidden" name="action" value="update_student_name">
        <label for="student_id">ID студента:</label>
        <input type="number" id="student_id" name="student_id" required>
        
        <label for="new_name">Новое имя студента:</label>
        <input type="text" id="new_name" name="new_name" required>
        
        <button type="submit">Обновить имя</button>
    </form>

    <h2>Добавить нового преподавателя</h2>
    <form method="POST">
        <input type="hidden" name="action" value="add_teacher">
        <label for="teacher_name">Имя преподавателя:</label>
        <input type="text" id="teacher_name" name="name" required>
        
        <button type="submit">Добавить преподавателя</button>
    </form>

    <h2>Добавить группу</h2>
    <form method="POST">
        <input type="hidden" name="action" value="add_group">
        <input type="text" name="name" placeholder="Название группы" required>
        <button type="submit">Добавить</button>
    </form>

    <h2>Фильтрация студентов по группе</h2>
    <form method="POST">
        <input type="hidden" name="action" value="filter_students_by_group">
        <label for="group_id">Выберите группу:</label>
        <select id="group_id" name="group_id" required>
            <option value="">Выберите группу</option>
            <?php
            $stmt = $pdo->query("SELECT id, name FROM groups");
            $groups = $stmt->fetchAll();

            foreach ($groups as $group) {
                echo "<option value='{$group['id']}'>{$group['name']}</option>";
            }
            ?>
        </select>
        <button type="submit">Показать студентов</button>
    </form>

    <h2>Добавить новый курс</h2>
    <form method="POST">
        <input type="hidden" name="action" value="add_course">
        <label for="name">Название курса:</label>
        <input type="text" id="name" name="name" required>
        
        <label for="teacher_id">Преподаватель:</label>
        <select id="teacher_id" name="teacher_id" required>
            <option value="" disabled selected>Выберите преподавателя</option>
            <?php
            $teachers = getOptions($pdo, 'teachers', 'id', 'name');
            foreach ($teachers as $teacher) {
                echo "<option value=\"{$teacher['id']}\">{$teacher['name']}</option>";
            }
            ?>
        </select>
        
        <button type="submit">Добавить курс</button>
    </form>

    <h2>Удалить курс</h2>
    <form method="POST">
        <input type="hidden" name="action" value="delete_course">
        <label for="course_id">Выберите курс для удаления:</label>
        <select id="course_id" name="course_id" required>
            <option value="">Выберите курс</option>
            <?php
            $stmt = $pdo->query("SELECT id, name FROM courses");
            $courses = $stmt->fetchAll();

            foreach ($courses as $course) {
                echo "<option value='{$course['id']}'>{$course['name']}</option>";
            }
            ?>
        </select>
        <button type="submit">Удалить курс</button>
    </form>

    <h2>Студенты, зарегистрированные на несколько курсов</h2>
    <form method="POST">
        <input type="hidden" name="action" value="filter_students_multiple_courses">
        <button type="submit">Показать студентов</button>
    </form>

    <h2>Привязать студента к группе</h2>
    <form method="POST">
        <input type="hidden" name="action" value="assign_group">
        <label for="student_id">Выберите студента:</label>
        <select name="student_id" id="student_id" required>
            <option value="" disabled selected>Выберите студента</option>
            <?php
            $students = getOptions($pdo, 'students', 'id', 'name');
            foreach ($students as $student) {
                echo "<option value=\"{$student['id']}\">{$student['name']}</option>";
            }
            ?>
        </select>
        <label for="group_id">Выберите группу:</label>
        <select name="group_id" id="group_id" required>
            <option value="" disabled selected>Выберите группу</option>
            <?php
            $groups = getOptions($pdo, 'groups', 'id', 'name');
            foreach ($groups as $group) {
                echo "<option value=\"{$group['id']}\">{$group['name']}</option>";
            }
            ?>
        </select>
        <button type="submit">Привязать</button>
    </form>

    <h2>Регистрация студента на курс</h2>
    <form method="POST">
        <input type="hidden" name="action" value="register_course">
        <label for="student_id">Студент:</label>
        <select name="student_id" id="student_id" required>
            <option value="" disabled selected>Выберите студента</option>
            <?php
            $students = getOptions($pdo, 'students', 'id', 'name');
            foreach ($students as $student) {
                echo "<option value=\"{$student['id']}\">{$student['name']}</option>";
            }
            ?>
        </select>
        <label for="course_id">Курс:</label>
        <select name="course_id" id="course_id" required>
            <option value="" disabled selected>Выберите курс</option>
            <?php
            $courses = getOptions($pdo, 'courses', 'id', 'name');
            foreach ($courses as $course) {
                echo "<option value=\"{$course['id']}\">{$course['name']}</option>";
            }
            ?>
        </select>
        <button type="submit">Зарегистрировать</button>
    </form>

    <?php
    showCoursesWithStudentCount($pdo);
    showTeachersWithCourses($pdo);
    showStudentsWithoutGroups($pdo);
    showTeachersWithStudentCount($pdo);
    showStudents($pdo);
    showGroups($pdo);
    showStudentsWithGroups($pdo);
    ?>
</body>
</html>
