<?php

$wl = JSON::load("localization/".ARG_3.".json");
print JSON::encode($wl[ARG_2]);

?>