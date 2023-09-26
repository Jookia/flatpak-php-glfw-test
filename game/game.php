<?php
	echo "################################################\n";
	echo "##     WASD, mouse to move     T/G up/down    ##\n";
	echo "##      F toggle wireframe    ESC to exit     ##\n";
	echo "##    Mousewheel to add cubes where you are   ##\n";
	echo "## any file size change exits the program     ##\n";
	echo "##     (for use in shell script loop)         ##\n";
	echo "################################################\n";

	$monitor_width=900;
	$monitor_height=900;
	$lastX = $monitor_width / 2;
	$lastY = $monitor_height / 2;

	require __DIR__ . '/99_example_helpers.php';
	use GL\Math\{GLM, Vec3, Vec4, Mat4, Vec2};
	use GL\Buffer\FloatBuffer;

	if (!glfwInit()) {	throw new Exception('GLFW could not be initialized!');}
	echo glfwGetVersionString() . PHP_EOL;
	glfwWindowHint(GLFW_RESIZABLE, GL_TRUE);
	glfwWindowHint(GLFW_CONTEXT_VERSION_MAJOR, 4);
	if (!$window = glfwCreateWindow($monitor_width, $monitor_height, "04")) {throw new Exception('OS Window could not be initialized!');}
	glfwMakeContextCurrent($window);
	glfwSwapInterval(1);

	glfwSetKeyCallback($window, function ($key, $scancode, $action, $mods) use ($window, &$wireframe, &$useNormalMap) {
		if ($key == GLFW_KEY_ESCAPE && $action == GLFW_PRESS) { glfwSetWindowShouldClose($window, true); }
	});
	glfwSetCursorPosCallback($window, function ($xpos, $ypos) use ($window, &$cameraRotation, &$lastX, &$lastY, &$firstMouse) {
		if (glfwGetMouseButton($window, GLFW_MOUSE_BUTTON_LEFT) != GLFW_PRESS) {
			glfwSetInputMode($window, GLFW_CURSOR, GLFW_CURSOR_NORMAL);
			$firstMouse = true;
			return;
		}
		glfwSetInputMode($window, GLFW_CURSOR, GLFW_CURSOR_DISABLED);
		if (!isset($firstMouse)) {
			$lastX = $xpos;
			$lastY = $ypos;
			$firstMouse = false;
		}

		$xoffset = $xpos - $lastX;
		$yoffset = $lastY - $ypos;
		$lastX = $xpos;
		$lastY = $ypos;

		$cameraRotation->x = $cameraRotation->x - $xoffset * 0.3;
		$cameraRotation->y = $cameraRotation->y + $yoffset * 0.3;
	});
	glfwSetCharModsCallback($window, function($codepoint, $mods) {
		global $keypressed;
		$keypressed=mb_chr($codepoint);
	});

	$cubeShader = ExampleHelper::compileShader("#version 330 core
		layout (location = 0) in vec3 a_position;
		layout (location = 1) in vec2 a_uv;
		layout (location = 2) in mat4 a_model;
		out vec2 v_uv;
		uniform mat4 view;
		uniform mat4 projection;
		void main(){
			v_uv = a_uv;
			gl_Position = projection * view * a_model * vec4(a_position, 1.0f);
		}",
		"#version 330 core
		out vec4 fragment_color;
		in vec2 v_uv;
		uniform sampler2D logo;
		void main(){
			fragment_color = vec4(texture(logo, v_uv).rgb, 0.1f) * vec4(v_uv, 0.9f  , 0.1f);
		}");//$matrices->reserve($c3size * $ c3size * $c3size *  16 );







	function putcube($x,$y,$z){
		global $matrices,$threeDgrid,$gridmax;
		$m = new Mat4;
		$p = new Vec3($x*1 , $z , $y*1 );
		$m->translate(($p ) );
		$matrices->pushMat4($m);
		$threeDgrid[$x][$y][$z]=1;
		if (!isset($gridmax[$x][$y]) | (isset($gridmax[$x][$y]) && $gridmax[$x][$y]<$z)){
			$gridmax[$x][$y]=$z;
		}
		global $matrices_has_changed;
		$matrices_has_changed=1;
	}
	function putfloor($floorsize){
		for ($y = 0; $y < $floorsize; $y++) {
		for ($x = 0; $x < $floorsize; $x++) {
			putcube($x,$y,0);
		}}
	}
	function bigcube($xstart,$ystart,$w){
		for ($y=$ystart; $y < $w; $y++) {
		for ($x=$xstart; $x < $w; $x++) {
		for ($z = 0;     $z < $w; $z++) {
			putcube($x,$y,$z);
		}}}
	}
	function addcube($x,$y){
		global $gridmax;
		putcube($x,$y,$gridmax[$x][$y]+1);
	}
	function putline($x,$y,$z){	//$dx=abs($x2-$x1);	//$dy=abs($y2-$y1);	//$dz=abs($z2-$z1);
		for ($a=0;$a<$z;$a++)	putcube($x,$y,$a);
	}



	$matrices = 	new FloatBuffer;
	$view = 		new Mat4;
	$projection = 	new Mat4;

	$threeDgrid=[];
	$floorsize=250;
	$playerstart_x=10.0;
	$playerstart_y=10.0;
	$playerstart_z=120.0;
	$playerstart_LR=50.0;
	$playerstart_UD=-20.0;


	putfloor($floorsize);
	bigcube(0,0,3);

	$h=50;
	for ($y=0;$y<250;$y++){
		putline(rand(0,$floorsize),rand(0,$floorsize),rand(0,$h));
	}

	$g=[];
	$g[]="x                                  99999999x";
	$g[]="                 2                     9   9";
	$g[]="                                           9";
	$g[]="                           2               8";
	$g[]="       4                               9   6";
	$g[]="       4                               9   6";
	$g[]="       4                               9   6";
	$g[]="       4                               9   6";
	$g[]="       4                               9   6";
	$g[]="       4                               9   6";
	$g[]="       4                               9   6";
	$g[]="       4                               9   6";
	$g[]="       4                               9   6";
	$g[]="       4                               9   6";
	$g[]="       4                               9   6";
	$g[]="              5                            4";
	$g[]="                                       9   2";
	$g[]="5                         6            9   1";
	$g[]="5      6                               9   1";
	$g[]="x555555555555555555555444444444333333333322x";
	for ($y=0;$y<count($g);$y++){
	for ($x=0;$x<strlen($g[0]);$x++){
		$v=$g[$y][$x];
		if ($v==" ") continue;
		if ($v=="x") $v=50;
		for ($z=0;$z<$v;$z++){
			putcube($x,$y,$z);
		}
	}}




	$texture_png="phplogo.png";

	$view->translate(new Vec3($playerstart_x, $playerstart_z, $playerstart_y ));
	$projection->perspective(glm::radians(70.0), $monitor_width/ $monitor_height, 0.1, 32000.0);
	$cameraRotation = new Vec2($playerstart_LR, $playerstart_UD);
	$texture = ExampleHelper::loadTexture($texture_png);

	glViewport(0, 0, $monitor_height, $monitor_height);
	glEnable(GL_DEPTH_TEST);
	glUseProgram($cubeShader);

	$counter=0;
	$frame=0;
	list($VAO, $VBO) = cube(0,1,0,0,1,0);

while (!glfwWindowShouldClose($window)){
	usleep(1000);
	$frame++;
	exitonfilesizechange();
	handlegravity();
	handlekeys();
	debug();
	calc_view();

	//if ($frame%60==0 )
	//putcube(4,4,rand(0,100));
	//addcube(rand(0,$floorsize),rand(0,$floorsize));

	glClearColor(0.8, 0.8   , 0.9      , 1);
	glClear(GL_COLOR_BUFFER_BIT);
	glClear(GL_DEPTH_BUFFER_BIT);
    glUniformMatrix4f(glGetUniformLocation($cubeShader, "view"), 		GL_FALSE, $eye);
    glUniformMatrix4f(glGetUniformLocation($cubeShader, "projection"), 	GL_FALSE, $projection);
    glBindVertexArray($VAO);
    glDrawArraysInstanced(GL_TRIANGLES, 0, 36, $matrices->size() / 16);
    glfwSwapBuffers($window);
    glfwPollEvents();
	if ($matrices_has_changed){
		$matrices_has_changed=0;
		reload_matrices();
	}
}


glDeleteVertexArrays(1, $VAO);
glDeleteBuffers(1, $VBO);

ExampleHelper::stop($window);




function reload_matrices(){
	global $EBO,$matrices,$VAO;
	glGenBuffers(1, $EBO);
	glBindBuffer(GL_ARRAY_BUFFER, $EBO);
	glBufferData(GL_ARRAY_BUFFER, $matrices, GL_STATIC_DRAW);
	glBindVertexArray($VAO);
	glEnableVertexAttribArray(2);
	glVertexAttribPointer(2, 4, GL_FLOAT, GL_FALSE, GL_SIZEOF_FLOAT * 16, 0);
	glEnableVertexAttribArray(3);
	glVertexAttribPointer(3, 4, GL_FLOAT, GL_FALSE, GL_SIZEOF_FLOAT * 16, (GL_SIZEOF_FLOAT * 4) * 1);
	glEnableVertexAttribArray(4);
	glVertexAttribPointer(4, 4, GL_FLOAT, GL_FALSE, GL_SIZEOF_FLOAT * 16, (GL_SIZEOF_FLOAT * 4) * 2);
	glEnableVertexAttribArray(5);
	glVertexAttribPointer(5, 4, GL_FLOAT, GL_FALSE, GL_SIZEOF_FLOAT * 16, (GL_SIZEOF_FLOAT * 4) * 3);
	glVertexAttribDivisor(2, 1);
	glVertexAttribDivisor(3, 1);
	glVertexAttribDivisor(4, 1);
	glVertexAttribDivisor(5, 1);
	glBindVertexArray(0);
}

function handlegravity(){
	global $userpos_x,$userpos_y,$userpos_z;
	global $userpos_zspeed;
	global $gravity;
	global $threeDgrid;
	global $gridmax;
	global $view;

	if (!isset($gravity)) $gravity=1;
	if ($gravity==0) return;

	if ($userpos_z< -30){
		$userpos_z=10;
		$userpos_zspeed=0;
	}
	$ux=round($userpos_x);
	$uy=round($userpos_y);
	$uz=round($userpos_z);

	/*echo "user z: $uz\n";	echo "3d[".($uz)."]: ";	if (isset($threeDgrid[$ux][$uy][$uz])) echo $threeDgrid[$ux][$uy][$uz];
	else echo "[]";	echo "\n";	echo "3d[".($uz-1)."]: ";	if (isset($threeDgrid[$ux][$uy][$uz-1])) echo $threeDgrid[$ux][$uy][$uz-1];
	else echo "[]";	echo "\n";	echo "3d[".($uz-2)."]: ";	if (isset($threeDgrid[$ux][$uy][$uz-2])) echo $threeDgrid[$ux][$uy][$uz-2];
	else echo "[]";	echo "\n";*/

	if (isset($threeDgrid[$ux][$uy][$uz]) && $threeDgrid[$ux][$uy][$uz]==1){
		echo "in_cube ($uz)\n";
		$userpos_zspeed= 0.0;
		$userpos_z=$gridmax[$ux][$uy]+2;
	}
	elseif (isset($threeDgrid[$ux][$uy][$uz-1]) && $threeDgrid[$ux][$uy][$uz-1]==1){
		$userpos_zspeed=0.0;
	}
	elseif (isset($threeDgrid[$ux][$uy][$uz-2]) && $threeDgrid[$ux][$uy][$uz-2]==1){
		$userpos_zspeed=0.0;
	}


	else if (!isset($threeDgrid[$ux][$uy][$uz-1]) | (isset($threeDgrid[$ux][$uy][$uz-1]) && $threeDgrid[$ux][$uy][$uz-1]==0)){
		$userpos_zspeed += -0.01     ;
	}
	$userpos_z =  $userpos_z + $userpos_zspeed;
	$view[13]=$userpos_z;
}


	function cube() {
		$verticies = new FloatBuffer([
		-0.5, -0.5, -0.5,  0.0, 0.0,
		 0.5, -0.5, -0.5,  1.0, 0.0,
		 0.5,  0.5, -0.5,  1.0, 1.0,
		 0.5,  0.5, -0.5,  1.0, 1.0,
		-0.5,  0.5, -0.5,  0.0, 1.0,
		-0.5, -0.5, -0.5,  0.0, 0.0,

		-0.5, -0.5,  0.5,  0.0, 0.0,
		 0.5, -0.5,  0.5,  1.0, 0.0,
		 0.5,  0.5,  0.5,  1.0, 1.0,
		 0.5,  0.5,  0.5,  1.0, 1.0,
		-0.5,  0.5,  0.5,  0.0, 1.0,
		-0.5, -0.5,  0.5,  0.0, 0.0,

		-0.5,  0.5,  0.5,  1.0, 0.0,
		-0.5,  0.5, -0.5,  1.0, 1.0,
		-0.5, -0.5, -0.5,  0.0, 1.0,
		-0.5, -0.5, -0.5,  0.0, 1.0,
		-0.5, -0.5,  0.5,  0.0, 0.0,
		-0.5,  0.5,  0.5,  1.0, 0.0,

		 0.5,  0.5,  0.5,  1.0, 0.0,
		 0.5,  0.5, -0.5,  1.0, 1.0,
		 0.5, -0.5, -0.5,  0.0, 1.0,
		 0.5, -0.5, -0.5,  0.0, 1.0,
		 0.5, -0.5,  0.5,  0.0, 0.0,
		 0.5,  0.5,  0.5,  1.0, 0.0,

		-0.5, -0.5, -0.5,  0.0, 1.0,
		 0.5, -0.5, -0.5,  1.0, 1.0,
		 0.5, -0.5,  0.5,  1.0, 0.0,
		 0.5, -0.5,  0.5,  1.0, 0.0,
		-0.5, -0.5,  0.5,  0.0, 0.0,
		-0.5, -0.5, -0.5,  0.0, 1.0,

		-0.5,  0.5, -0.5,  0.0, 1.0,
		 0.5,  0.5, -0.5,  1.0, 1.0,
		 0.5,  0.5,  0.5,  1.0, 0.0,
		 0.5,  0.5,  0.5,  1.0, 0.0,
		-0.5,  0.5,  0.5,  0.0, 0.0,
		-0.5,  0.5, -0.5,  0.0, 1.0
		]);

        glGenVertexArrays(1, $VAO);
        glGenBuffers(1, $VBO);

        glBindVertexArray($VAO);
        glBindBuffer(GL_ARRAY_BUFFER, $VBO);
        glBufferData(GL_ARRAY_BUFFER, $verticies, GL_STATIC_DRAW);

        glVertexAttribPointer(0, 3, GL_FLOAT, GL_FALSE, GL_SIZEOF_FLOAT * 5, 0);
        glEnableVertexAttribArray(0);

        glVertexAttribPointer(1, 2, GL_FLOAT, GL_FALSE, GL_SIZEOF_FLOAT * 5 , GL_SIZEOF_FLOAT * 3);
        glEnableVertexAttribArray(1);

        glBindBuffer(GL_ARRAY_BUFFER, 0);
        glBindVertexArray(0);

        return [$VAO, $VBO];
    }



	function calc_view(){
		global $eye,$view,$cameraRotation,$forward;
		$eye = $view->copy();
		$eye->rotate(GLM::radians($cameraRotation->x), new Vec3(0.0, 1.0, 0.0));
		$eye->rotate(GLM::radians($cameraRotation->y), new Vec3(1.0, 0.0, 0.0));
		$eye->inverse();
		$col = $eye->col(2);
		$forward = new Vec3($col->x, $col->y, $col->z);
	}
function handlekeys(){
	global $window, $view, $forward, $action, $speedmultiplier, $VAO, $VBO,$key;

	$moved=0;
    if (glfwGetKey($window, GLFW_KEY_W) == GLFW_PRESS) {     	$moved=1; $view->translate($forward * -1); }
    else if (glfwGetKey($window, GLFW_KEY_S) == GLFW_PRESS) {	$moved=1; $view->translate($forward); }
    else if (glfwGetKey($window, GLFW_KEY_A) == GLFW_PRESS) {	$moved=1; $view->translate($forward->cross(new Vec3(0.0, 1.0, 0.0))); }
    else if (glfwGetKey($window, GLFW_KEY_D) == GLFW_PRESS) {	$moved=1; $view->translate($forward->cross(new Vec3(0.0, 1.0, 0.0)) * -1); }
    else if (glfwGetKey($window, GLFW_KEY_Y) == GLFW_PRESS) {	$moved=1; $view[13]+=1;	}
    else if (glfwGetKey($window, GLFW_KEY_H) == GLFW_PRESS) {	$moved=1; $view[13]-=1;	}

	if ($key == GLFW_KEY_ESCAPE && $action == GLFW_PRESS){
		glfwSetWindowShouldClose($window, true);
		exit;
	}
 
	glfwSetScrollCallback($window, function($x, $y) {
		echo "Mouse scrolled: " . $x . ", " . $y . PHP_EOL;
		global $speedmultiplier;
		if ($y==1)	$speedmultiplier*=1.2;
		if ($y==-1)	$speedmultiplier/=1.2;

		global $userpos_x,$userpos_y,$userpos_z;
		putcube($userpos_x,$userpos_y,$userpos_z);
	});
	if (glfwGetKey($window, GLFW_KEY_K) == GLFW_PRESS) {
		glDeleteVertexArrays(1, $VAO);
		glDeleteBuffers(1, $VBO);
		ExampleHelper::stop($window);
		exit;
	}

	global $keypressed;
	if ($keypressed != ""){
		echo "keypressed=$keypressed\n";

		if ($keypressed==7) 	$p1*=1.2;
		if ($keypressed==9) 	$p1/=1.2;
		if ($keypressed=="+")	$speedmultiplier*=1.2;
		if ($keypressed=="-")	$speedmultiplier/=1.2;

		if ($keypressed=="4"|| $keypressed=="6" || $keypressed=="2" || $keypressed=="8"){
			if ($keypressed=="4")	$lookat1 -=3.2;
			if ($keypressed=="6")	$lookat1 +=3.2;
			if ($keypressed=="2")	$lookat2 -=3.2;
			if ($keypressed=="8")	$lookat2 +=3.2;
		}

		if ($keypressed=="g"){
			global $gravity;
			$gravity==1 ? $gravity=0:$gravity=1;
			echo "gravity=$gravity\n";
		}
		if ($keypressed=="f"){
			global $wireframe;
			$wireframe==1 ? $wireframe=0:$wireframe=1;
			if ($wireframe)	glPolygonMode(GL_FRONT_AND_BACK, GL_LINE);
			else			glPolygonMode(GL_FRONT_AND_BACK, GL_FILL);
		}

		$keypressed="";
	}


	global $userpos_x,$userpos_y,$userpos_z;

	$userpos_x=$view[12];
	$userpos_y=$view[14];
	$userpos_z=$view[13];

	if ($moved)	echo "USER x,y,z = ".round($userpos_x).", ".round($userpos_y).", ".round($userpos_z)."\n";
}
function debug(){
	global $view,$projection,$eye,$frame;
	if ($frame % 120  ==0){
	//	echo "VIEW: \n";	 	print_r($view);
	//	echo "projection:\n";	 	print_r($projection);
	//	echo "eye:\n";	print_r($eye);
	//	echo "frame: $frame \n";
	}
	//print_r(get_defined_vars());
	if ($frame%5400==0) echo "mem: ".memory_get_usage()."\n";
}

function exitonfilesizechange(){
	global $thisfilesize;
	if (!isset($thisfilesize))	$thisfilesize=filesize(__FILE__);
	clearstatcache();
	if (filesize(__FILE__) !==$thisfilesize) exit;
}

function ansirowcol($row,$col=0){ return "\033[".$row.";".$col."f";}
function ansicol($r){ return  "\033[".$r.";G";}
function ansiclear(){ return "\033[2J";}
function ansierasettoendofline(){ return "\033[0K";}
function ansieraseline(){ return "\033[2K";}
