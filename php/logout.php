<?php
session_start();
session_destroy();
header("Location: ../html/auth_login.html");
exit();
