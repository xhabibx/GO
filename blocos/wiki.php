<?php
	$chave = md5($idioma . $mensagens['message']['text']);

	if ($redis->exists('wiki:' . $chave)) {
		$mensagem = $redis->get('wiki:' . $chave);
	} else if (isset($texto[1])) {
		$nomeArtigo = str_ireplace($texto[0], '', $mensagens['message']['text']);

		$requisicao = 'https://' . $idioma . '.wikipedia.org/w/api.php?action=query&prop=extracts&format=json&exchars=480&exsectionformat=plain&explaintext=&redirects=&titles=' . urlencode($nomeArtigo);

		$resultado = json_decode(enviarRequisicao($requisicao), true);

			$paginas = $resultado['query']['pages'];
		 $idPagina = array_keys($paginas);

		 if ($idPagina[0] != -1) {
			 	 $tituloPagina = $paginas[$idPagina[0]]['title'];
			 $conteudoPagina = $paginas[$idPagina[0]]['extract'];
			 			$urlPagina = 'https://' . $idioma . '.wikipedia.com/wiki/' . str_replace(' ', '_', $tituloPagina);
			 			 $mensagem = '🗄 <a href="' . $urlPagina . '">' . $tituloPagina . '</a>' . "\n\n" . $conteudoPagina;
		} else {
			$mensagem = ERROS[$idioma][SEM_RSULT];
		}

		$redis->setex('wiki:' . $chave, 3600, $mensagem);
	} else {
		$mensagem = '📚: /wiki Brasil';
	}

	sendMessage($mensagens['message']['chat']['id'], $mensagem, $mensagens['message']['message_id'], null, true);
