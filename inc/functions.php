<?php
function get_project_list() {
    include('connection.php');

    try {
        return $db_connect->query("SELECT project_id, title, category FROM projects");
        //$stmt = $db_connect->prepare("SELECT project_id, title, category FROM projects");
        //$stmt->execute();
        //$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //return $items;
    } catch (Exception $e) {
        echo "Error!: " . $e->getMessage() . "</br>";
        return false;
    }
}
//$items = get_project_list();

function get_task_list($filter = null) {
    include('connection.php');
    $sql = "SELECT tasks.*, projects.title as project FROM tasks JOIN projects ON tasks.project_id = projects.project_id";

    $where = '';
    if(is_array($filter)) {
        switch ($filter[0]) {
            case 'project':
                $where = ' WHERE projects.project_id = ?';
                break;
            case 'category':
                $where = ' WHERE category = ?';
                break;
            case 'date':
                $where = ' WHERE date >= ? AND date <= ?';
                break;
        }
    }

    $orderBy = ' ORDER BY date DESC';
    if($filter) {
        $orderBy = ' ORDER BY projects.title ASC, date DESC';
    }
    try {
        $stmt = $db_connect->prepare($sql . $where . $orderBy);
        if(is_array($filter)) {
            $stmt->bindValue(1, $filter[1]);
            if($filter[0] == 'date') {
                $stmt->bindValue(2, $filter[2], PDO::PARAM_STR);
            }
        }
        $stmt->execute();
    } catch (Exception $e) {
        echo "Error!: " . $e->getMessage() . "</br>";
        return false;
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function add_project($title, $category, $project_id = null) {
    include('connection.php');
    if($project_id) {
        $sql = "UPDATE projects SET title = :title, category = :category WHERE project_id = :id";
    } else {
        $sql = "INSERT INTO projects (title, category) VALUES(:title, :category)";
    }
    try {
        $stmt = $db_connect->prepare($sql);    
        if($project_id) {
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            $stmt->bindValue(':id', $project_id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt->execute(array(':title' => $title, ':category' => $category));
        }
    } catch(Exception $e) {
        echo "Error: " . $e->getMessage() . "<br />";
        return false;
    }
    return true;
}

function get_project($project_id) {
    include('connection.php');
    $sql = "SELECT * FROM projects WHERE project_id = :project_id";
    try {
        $stmt = $db_connect->prepare($sql);
        $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->execute();
    } catch(Exception $e) {
        echo "Error: " . $e->getMessage() . "<br />";
        return false;
    }
    return $stmt->fetch();
}

function get_task($task_id) {
    include('connection.php');
    $sql = "SELECT task_id, title, date, time, project_id FROM tasks WHERE task_id = :task_id";
    try {
        $stmt = $db_connect->prepare($sql);
        $stmt->bindValue(':task_id', $task_id, PDO::PARAM_INT);
        $stmt->execute();
    } catch(Exception $e) {
        echo "Error: " . $e->getMessage() . "<br />";
        return false;
    }
    return $stmt->fetch();
}

function delete_task($task_id) {
    include('connection.php');
    $sql = "DELETE FROM tasks WHERE task_id = :task_id";
    try {
        $stmt = $db_connect->prepare($sql);
        $stmt->bindValue(':task_id', $task_id, PDO::PARAM_INT);
        $stmt->execute();
    } catch(Exception $e) {
        echo "Error: " . $e->getMessage() . "<br />";
        return false;
    }
    return true;
}

function delete_project($project_id) {
    include('connection.php');
    $sql = "DELETE FROM projects WHERE project_id = :project_id AND project_id NOT IN (SELECT project_id FROM tasks)";
    try {
        $stmt = $db_connect->prepare($sql);
        $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->execute();
    } catch(Exception $e) {
        echo "Error: " . $e->getMessage() . "<br />";
        return false;
    }
    if($stmt->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
}

function add_task($project_id, $title, $date, $time, $task_id = null) {
    include('connection.php');
    if($task_id) {
        $sql = "UPDATE tasks SET project_id = :project_id, title = :title, date = :date, time = :time WHERE task_id = :task_id";
    } else {
        $sql = "INSERT INTO tasks (project_id, title, date, time) VALUES(:project_id, :title, :date, :time)";
    }
    try {
        $stmt = $db_connect->prepare($sql);
        if($task_id) {
            $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindValue(':time', $time, PDO::PARAM_INT);
            $stmt->bindValue(':task_id', $task_id, PDO::PARAM_INT);
            $stmt->execute();

        } else {
            $stmt->execute(array(':project_id' => $project_id, ':title' => $title, ':date' => $date, ':time' => $time));
        }
    } catch(Exception $e) {
        echo "Error: " . $e->getMessage() . "<br />";
        return false;
    }
    return true;
}
?>