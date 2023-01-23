<?php
	error_reporting(E_ALL);
	//Diretório
	//Como o diretório é lido duas vezes e cada $dir só pode ser lido uma vez
	//Foram criadas duas variáveis para a leitura
	$path = 'C:\\COBPOP\\Arquivos\\Retornos\\Pre-Critica\\';
	$dir = dir($path);
	$dir2 = dir($path);
	//Extensões permitidas
	$allowed_extensions = array('ret', 'RET', 'Ret', 'srq', 'srt');
	//Contando arquivos de retorno
	$fls = array();
	while($fs = $dir->read()):
		if (in_array(pathinfo($fs, PATHINFO_EXTENSION), $allowed_extensions)) :
			$fls[] = $fs;
		endif;
	endwhile;

	$flsLen = count($fls);

	if ($flsLen > 0) :
		echo '<form method="post" action="processamento-precritica.php">';
		echo '<h4><span class="glyphicon glyphicon-list-alt"></span> Retornos disponíveis para processamento ('.$flsLen.')</h4>';
		echo '<ul class="list-group">';
		while ($file = $dir2->read()) :
			if (in_array(pathinfo($file, PATHINFO_EXTENSION), $allowed_extensions)) :
				echo '<li class="list-group-item">'.$file.'<span class="pull-right badge"><span class="glyphicon glyphicon-open"></span> '.filesize($path.$file).' Kb</span></li>';
			endif;
		endwhile;
		echo '</ul>';
		echo '<button id="create-rem" class="btn btn-primary"><span class="glyphicon glyphicon-refresh"></span> Processar Retornos</button>';
	else :
		echo '<h4><span class="glyphicon glyphicon-alert"></span> Nenhum retorno encontrado!</h4>';
	endif;