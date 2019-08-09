window.addEvent('domready', function() {

	$('form_entrada').addEvent('submit', function(evento) {

			evento.stop();
	
			$divExecucao = $('conteudo_execucao');	
			$divExecucao.empty().addClass('carregando');
			$divExecucao.set('html', 'Carregando...');
			
			$divPipeline = $('conteudo_pipeline');
			$divPipeline.empty().addClass('carregando');
			$divPipeline.set('html', 'Carregando...');
			
			$divRegistradores = $('conteudo_registradores');
			$divRegistradores.empty().addClass('carregando');
			$divRegistradores.set('html', 'Carregando...');
			
			$divDados = $('conteudo_dados');
			$divDados.empty().addClass('carregando');
			$divDados.set('html', 'Carregando...');
	
			$divInstrucoes = $('conteudo_instrucoes');
			$divInstrucoes.empty().addClass('carregando');
			$divInstrucoes.set('html', 'Carregando...');
			
			this.set('send', { onComplete: function(resposta) {
	
				$divExecucao.removeProperty('class');
				$divPipeline.removeProperty('class');
				$divRegistradores.removeProperty('class');
				$divDados.removeProperty('class');
				$divInstrucoes.removeProperty('class');
	
				var json = JSON.decode(resposta, true);
	
				if (json.sucesso) {
					$divExecucao.set('html', json.execucao);
					$divPipeline.set('html', json.pipeline);
					$divRegistradores.set('html', json.registradores);
					$divDados.set('html', json.dados);
					$divInstrucoes.set('html', json.instrucoes);
				} else {
					$divExecucao.set('html', json.erro);
					$divPipeline.set('html', json.erro);
					$divRegistradores.set('html', json.erro);
					$divDados.set('html', json.erro);
					$divInstrucoes.set('html', json.erro);
				}
	
			}});

		if ($('text_entrada').value != '') {
			this.send();
		} else {
			$divExecucao.set('html', '');
			$divPipeline.set('html', '');
			$divRegistradores.set('html', '');
			$divDados.set('html', '');
			$divInstrucoes.set('html', '');
			
		}

	});
});