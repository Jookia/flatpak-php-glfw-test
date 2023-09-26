#!/bin/sh
cd /app/bin/
exec php -dextension=glfw.so game.php
