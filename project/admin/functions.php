<?php
// In use functions

function checkLogin()
{
    if (isset($_SESSION['user']['user_id'])) {
        return true;
    } else {
        return false;
    }
}
function check_exist($perm, $value, $pdo)
{
    $check_user_exist = $pdo->prepare("SELECT COUNT(*) FROM `tbl_users` WHERE `" . $perm . "` = :value");
    $check_user_exist->bindParam(':value', $value, PDO::PARAM_STR);
    $check_user_exist->execute();
    if ($check_user_exist->fetchColumn() > 0) {
        return true;
    } else {
        return false;
    }
}

// Login User
function login($username, $password, $pdo)
{
    try {
        $check_user_exist = $pdo->prepare("SELECT COUNT(*) FROM `tbl_users` WHERE `user_username` = :username");
        $check_user_exist->bindParam(':username', $username, PDO::PARAM_STR);
        $check_user_exist->execute();
        if ($check_user_exist->fetchColumn() > 0) {
            $check_failed_attempts = $pdo->prepare("SELECT `user_login_attempts` FROM `tbl_users` WHERE `user_username` = :username");
            $check_failed_attempts->bindParam(':username', $username, PDO::PARAM_STR);
            $check_failed_attempts->execute();
            $failed_attempts = $check_failed_attempts->fetch(PDO::FETCH_ASSOC)['user_login_attempts'];
            if ($failed_attempts >= 3) {
                return array('error' => 'Account has been blocked !');
            } else {
                // Verify Password
                $check_login_user = $pdo->prepare("SELECT `user_password`, `user_last_login`, `user_created_at` FROM `tbl_users` WHERE `user_username` = :username");
                $check_login_user->bindParam(':username', $username, PDO::PARAM_STR);
                $check_login_user->execute();
                $this_login_user = $check_login_user->fetch(PDO::FETCH_ASSOC);

                if (isset($this_login_user) && password_verify($password, $this_login_user['user_password'])) {
                    date_default_timezone_set('America/Toronto');

                    if ((time() - $this_login_user['user_created_at']) > 3 * 24 * 60 * 60 && $this_login_user['user_last_login'] == null) {
                        return array('error' => 'Account Suspended');
                    }

                    $login_user = $pdo->prepare("SELECT `user_id`, `user_name`, `user_username`, `user_email`, `user_last_login`, `user_last_login`, `user_created_at` FROM `tbl_users` WHERE `user_username` = :username");
                    $login_user->bindParam(':username', $username, PDO::PARAM_STR);
                    $login_user->execute();

                    if ($login_user = $login_user->fetch(PDO::FETCH_ASSOC)) {
                        $_SESSION['user'] = $login_user;
                    }

                    $update_failed_attempts = $pdo->prepare('UPDATE `tbl_users` SET `user_login_attempts` = 0, `user_last_login` = ' . time() . ' WHERE `user_id` = :userId');
                    $update_failed_attempts->bindParam(':userId', $_SESSION['user']['user_id'], PDO::PARAM_INT);

                    $update_failed_attempts->execute();
                    return array('success' => 'Account has been logged in', 'user' => $_SESSION['user']);
                } else {
                    unset($_SESSION);
                    session_destroy();
                    $update_failed_attempts = $pdo->prepare('UPDATE `tbl_users` SET `user_login_attempts` = `user_login_attempts` + 1 WHERE `user_username` = :username');
                    $update_failed_attempts->bindParam(':username', $username, PDO::PARAM_STR);
                    $update_failed_attempts->execute();
                    return array('error' => ($failed_attempts < 2) ? 'Login failed ! ' : 'Login failed ! Account has been blocked !');
                }
            }
        } else {
            return array('error' => "Login failed !");
        }
    } catch (PDOException $error) {
        return array('error' => $error);
    }
}
// Create User
function create_user($username, $password, $name, $email, $pdo)
{
    try {
        if (
            !empty(trim($username)) &&
            !empty(trim($name)) &&
            filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            if (check_exist('user_username', $username, $pdo) || check_exist('user_email', $email, $pdo)) {
                return array('error' => "User already exists !");
            }
            $encryptedPassword = password_hash($password, PASSWORD_DEFAULT);

            $create_user_query = 'INSERT INTO `tbl_users` (`user_name`, `user_username`, `user_email`, `user_password`, `user_login_attempts`, `user_last_login`, `user_created_at`) ';
            $create_user_query .= 'VALUES (:name, :username, :email, :password, 0, null,' . time() . ');';
            $create_user = $pdo->prepare($create_user_query);
            $create_user->bindParam(':name', $name, PDO::PARAM_STR);
            $create_user->bindParam(':username', $username, PDO::PARAM_STR);
            $create_user->bindParam(':email', $email, PDO::PARAM_STR);
            $create_user->bindParam(':password', $encryptedPassword);
            $create_user->execute();

            if ($create_user->rowCount() > 0) {
                return array('success' => "User created successfully");
            } else {
                return array('error' => "User not created successfully");
            }
        } else {
            return array('error' => "Invalid requested inputs !");
        }
    } catch (PDOException $error) {
        return array('error' => $error);
    }
    return array('error' => $username);
}
// Edit User
function edit_user($username, $email, $name, $password, $pdo)
{
    if (
        empty(trim($username)) &&
        empty(trim($name)) &&
        filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        return 'Wrong Input !!';
    }

    if ($_SESSION['user']['user_username'] != $username) {
        if (check_exist('user_username', $username, $pdo)) {
            return 'Username exists !!';
        }
    } else {
        $username = null;
    }

    if ($_SESSION['user']['user_email'] != $email) {
        if (check_exist('user_email', $email, $pdo)) {
            return 'Email exists !!';
        }
    } else {
        $email = null;
    }

    if (empty(trim($name))) {
        $name = null;
    }
    if (empty(trim($password))) {
        $password = null;
    } else {
        $password = password_hash($password, PASSWORD_DEFAULT);
    }
    $update_user_query = "UPDATE `tbl_users`
            SET `user_name` =  COALESCE(:name, `user_name`),
            `user_username` = COALESCE(:username, `user_username`),
            `user_email` = COALESCE(:email, `user_email`),
            `user_password` = COALESCE(:password, `user_password`)
            WHERE `user_id` = :id";
    $update_user = $pdo->prepare($update_user_query);
    $update_user->bindParam(':name', $name, PDO::PARAM_STR);
    $update_user->bindParam(':username', $username, PDO::PARAM_STR);
    $update_user->bindParam(':email', $email, PDO::PARAM_STR);
    $update_user->bindParam(':password', $password);
    $update_user->bindParam(':id', $_SESSION['user']['user_id']);
    $update_user->execute();

    if ($update_user->rowCount() > 0) {
        if ($username != null) {
            $_SESSION['user']['user_username'] = $username;
        }
        if ($email != null) {
            $_SESSION['user']['user_email'] = $email;
        }
        if ($name != null) {
            $_SESSION['user']['user_name'] = $name;
        }
        return array('success' => 'Information Updated successfully');
    } else {
        return array('error' => 'Unable to Update Information');
    }
}
// Logout User
function logout()
{
    session_destroy();
    return array('success' => 'Account Logout Successfully');
}
