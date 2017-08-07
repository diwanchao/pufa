<?php

//echo "1111";die();


require dirname(__FILE__) . '/../../dh/Dh.php';
Dh::createApplication(dirname(__FILE__) . '/../app/config/main.php')->run();
