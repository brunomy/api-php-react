
			<ul class="aside_menu">
				<li><a href="<?php echo $sistema->seo_pages['minha-conta']['seo_url']; ?>"<?php if($pagina == 'minha-conta') echo ' class="active"'; ?>>Histórico de Pedidos</a></li>
				<li><a href="<?php echo $sistema->seo_pages['dados-pessoais']['seo_url']; ?>"<?php if($pagina == 'dados-pessoais') echo ' class="active"'; ?>>Alterar Dados Pessoais</a></li>
				<li><a href="<?php echo $sistema->seo_pages['endereco']['seo_url']; ?>"<?php if($pagina == 'endereco') echo ' class="active"'; ?>>Alterar Endereço</a></li>
				<li><a href="<?php echo $sistema->seo_pages['senha']['seo_url']; ?>"<?php if($pagina == 'senha') echo ' class="active"'; ?>>Alterar Senha</a></li>
				<li><a href="sair">Sair</a></li>
			</ul>
