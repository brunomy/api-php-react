<?php

  //DEFININDO NUMERO QUE APARECE NO WHATAPP DO RODAPÉ---------------
  $whatsapp_link = 'https://api.whatsapp.com/send/?phone=__number__&text=Ol%C3%A1,%20tenho%20interesse%20em%20produtos%20da%20Real%20Poker%0D%0A%0D%0A'.$utm_campaign;
  $whatsapp_numbers = array(
      array(
          'name'=>'Isnara',
          'number'=>'5511956535294',
      ),
      array(
          'name'=>'Daniele',
          'number'=>'5511971380712',
      ),
  );

  if(isset($_GET['vendedor'])){
    if($_GET['vendedor'] == 'isnara') $whatsapp_number = $whatsapp_numbers[0]['number'];
    if($_GET['vendedor'] == 'daniele') $whatsapp_number = $whatsapp_numbers[1]['number'];
  }else{
    $whatsapp_index = rand(0,1);
    $whatsapp_number = $whatsapp_numbers[$whatsapp_index]['number'];
  }

  if(!isset($_COOKIE['whatsapp'])){
      setcookie('whatsapp', $whatsapp_number, (time() + (60 * 24 * 3600)));
  }else{
      if($_COOKIE['whatsapp'] != $whatsapp_numbers[0]['number'] && $_COOKIE['whatsapp'] != $whatsapp_numbers[1]['number']){
          setcookie('whatsapp', $whatsapp_number, (time() + (60 * 24 * 3600)));
          $_COOKIE['whatsapp'] = $whatsapp_number;
      }
      $whatsapp_number = $_COOKIE['whatsapp'];
  }
  
?> 
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Real Poker</title>
  
  <!-- Vincula o CSS responsivo -->
  <link rel="stylesheet" href="style.css" />
  
  <!-- Se preferir carregar a fonte via Google Fonts, descomente o link abaixo e remova as regras @font-face do CSS -->
  <!-- <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet"> -->
  
  <!-- Google Tag Manager -->
  <script>
      (function(w,d,s,l,i){
          w[l]=w[l]||[];
          w[l].push({'gtm.start': new Date().getTime(), event:'gtm.js'});
          var f=d.getElementsByTagName(s)[0],
              j=d.createElement(s),
              dl=l!='dataLayer'?'&l='+l:'';
          j.async=true;
          j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;
          f.parentNode.insertBefore(j,f);
      })(window,document,'script','dataLayer','GTM-K3D5SV6');
  </script>
  <!-- End Google Tag Manager -->
</head>
<body>
  <!-- Google Tag Manager (noscript) -->
  <noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K3D5SV6"
    height="0" width="0" style="display:none;visibility:hidden"></iframe>
  </noscript>
  <!-- End Google Tag Manager (noscript) -->
  
  <!-- Header -->
  <header>
    <img src="logo-realpoker.png" alt="Logo Real Poker" class="logo" width="800" />
    <br><br>
    <h1>O padrão dos grandes torneios<br>agora no seu jogo!</h1>
    <p class="autoridade-sub">
      Fichas de Poker Profissionais, 100% Personalizadas<br>e com Segurança Anti-Falsificação<br>feitas para quem joga sério.
    </p>
    <div class="video-wrapper">
      <video controls autoplay muted loop playsinline>
        <source src="video-poker.mp4" type="video/mp4">
        Seu navegador não suporta vídeo em HTML5.
      </video>
    </div>
    
    <a href="https://wa.me/<?php echo $whatsapp_number; ?>?text=Quero%20falar%20com%20uma%20consultora%20sobre%20as%20fichas%20personalizadas" 
       target="_blank" 
       class="cta destaque-verde">
      FALAR COM UMA CONSULTORA AGORA
    </a>
  </header>
  
  <!-- Seção de Benefícios -->
  <section class="section-beneficios">
    <div class="proposta-beneficios">
      <div class="beneficios-imagem">
        <img src="fichas-beneficios.png" alt="Fichas de Poker Real Poker" />
      </div>
      <div class="proposta-beneficios">
        <div class="beneficio-box">
          <img src="icone-paleta.png" alt="Personalização" />
          <div>
            <strong>FICHAS 100% PERSONALIZADAS</strong><br>
            <small>Escolha borda, tamanho, cores, <br>escritas, grade e valores!</small>
          </div>
        </div>
        <div class="beneficio-box">
          <img src="icone-cadeado.png" alt="Segurança" />
          <div>
            <strong>TECNOLOGIA ANTI FALSIFICAÇÃO</strong><br>
            <small>Jogue com segurança: marcações <br>exclusivas em baixo relevo.</small>
          </div>
        </div>
        <div class="beneficio-box">
          <img src="icone-trofeu.png" alt="Torneios" />
          <div>
            <strong>QUALIDADE DE TORNEIO OFICIAIS</strong><br>
            <small>As mesmas fichas usadas no <br>WSOP Brazil e BSOP.</small>
          </div>
        </div>
      </div>
    </div>
    <a href="https://wa.me/<?php echo $whatsapp_number; ?>?text=Quero%20falar%20com%20uma%20consultora%20sobre%20as%20fichas%20personalizadas" 
       target="_blank" 
       class="cta destaque-verde">
      FALAR COM UMA CONSULTORA AGORA
    </a>
  </section>
  
  <!-- Seção Fabricante -->
  <section class="section-autoridade">
    <div class="container">
      <h1 class="autoridade-titulo">
        SOMOS FABRICANTE OFICIAL <br>DOS MAIORES TORNEIOS E CLUBES DO BRASIL
      </h1>
      <p class="autoridade-sub">
        Utilizadas por profissionais. Aprovadas por campeões.<br>
        Fabricadas por quem entende do jogo.
      </p>
      <div class="logos-torneios">
        <img src="logo-wsop.png" alt="World Series of Poker" />
        <img src="logo-bsop.png" alt="BSOP" />
        <img src="logo-h2.png" alt="H2 Club" />
        <img src="logo-nordeste.png" alt="Nordeste Poker Series" />
        <img src="logo-montecarlo.png" alt="Monte Carlo Poker Club" />
      </div>
    </div>
  </section>
  
  <!-- Galeria -->
  <section class="section">
    <h2>Nossas fichas já estão<br>na mesa de grandes nomes</h2>
    <div class="clientes-galeria">
      <img src="Cliente1.jpg" alt="Cliente 1">
      <img src="Cliente2.jpg" alt="Cliente 2">
      <img src="Cliente3.jpg" alt="Cliente 3">
    </div>
    <br><br><br>
  </section>
  
  <!-- Seção Fichas -->
  <section class="section-fichas">
    <div class="container">
      <h1>Fichas Personalizadas que Já<br>Estão Fazendo História nas Mesas</h1>
      <p class="autoridade-sub">
        Confira alguns modelos exclusivos já produzidos pela Real Poker<br>
        para clubes, torneios e jogadores exigentes.
      </p>
      <div class="gallery">
        <img src="fichas1.jpg" alt="Fichas">
        <img src="fichas2.jpg" alt="Botão Dealer">
        <img src="fichas3.jpg" alt="Fichas Luz Negra">
        <img src="fichas4.jpg" alt="Kit Completo">
        <img src="fichas5.jpg" alt="Fichas Luz Negra">
        <img src="fichas6.jpg" alt="Kit Completo">
      </div>
      <a href="https://wa.me/<?php echo $whatsapp_number; ?>?text=Quero%20falar%20com%20uma%20consultora%20sobre%20as%20fichas%20personalizadas" 
         target="_blank" 
         class="cta destaque-verde">
        FALAR COM UMA CONSULTORA AGORA
      </a>
    </div>
  </section>
  
  <!-- Seção Proposta -->
  <section class="section-proposta">
    <div class="container">
      <h2 class="proposta-titulo">SEU JOGO MERECE FICHAS À ALTURA DA SUA MESA</h2>
      <hr class="proposta-divisor" />
      <p class="proposta-sub">
        Entre agora em contato com uma consultora Real Poker e receba sua proposta personalizada para fichas exclusivas.
      </p>
      <div class="proposta-beneficios">
        <div class="beneficio-box">
          <img src="icone-caminhao.png" alt="Frete Grátis" />
          <div>
            <strong>FRETE GRÁTIS BRASIL*</strong><br>
            <small>Consulte Regiões</small>
          </div>
        </div>
        <div class="beneficio-box">
          <img src="icone-cartao.png" alt="10x Sem Juros" />
          <div>
            <strong>EM ATÉ 10X SEM JUROS</strong><br>
            <small>Nos cartões de crédito</small>
          </div>
        </div>
        <div class="beneficio-box">
          <img src="icone-desconto.png" alt="Desconto à vista" />
          <div>
            <strong>5% DE DESCONTO</strong><br>
            <small>à vista ou boleto</small>
          </div>
        </div>
      </div>
      <a href="https://wa.me/<?php echo $whatsapp_number; ?>?text=Quero%20falar%20com%20uma%20consultora%20sobre%20as%20fichas%20personalizadas" 
         target="_blank" 
         class="cta destaque-verde">
        FALAR COM UMA CONSULTORA AGORA
      </a>
    </div>
  </section>
  
    <!-- Rodapé -->
    <footer class="rodape-final">
      <div class="rodape-topo">
        <img src="logo-realpoker.png" alt="Real Poker" class="rodape-logo">
        <a href="https://www.instagram.com/real_poker" class="rodape-instagram" target="_blank">
          <img src="icone-instagram.png" alt="Instagram" class="rodape-icon">
          <span class="insta-text">
            Conheça o <br> nosso Instagram
          </span>
        </a>
      </div>
      <div class="rodape-rodape">
        <a href="https://www.realpoker.com.br" class="rodape-site" target="_blank">
          www.realpoker.com.br
        </a>
      </div>
    </footer>

</body>
</html>


