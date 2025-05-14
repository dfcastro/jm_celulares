@extends('layouts.public') {{-- USA O NOVO LAYOUT PÚBLICO --}}

@section('title', 'JM Celulares - Assistência Técnica e Acessórios em Monte Azul') {{-- Título específico da página inicial --}}

@section('content-public') {{-- TODO O CONTEÚDO DA PÁGINA INICIAL VEM AQUI --}}

    <section class="hero-section" style="background-color: #3A3A3A;">
        <div class="container">
            <h1 style="color: #FFA500; font-weight: bold; font-size: 3.5rem;">JM CELULARES</h1>
            <p class="lead text-white mb-3">Acessórios e Eletrônicos</p>
            <p class="text-white" style="font-size: 1.2rem;">MANUTENÇÃO EM SMARTPHONE APPLE & ANDROID</p>
            <div class="mt-4">
                <a href="{{ route('consulta.index') }}" class="btn btn-warning btn-lg me-sm-2 mb-2 mb-sm-0"><i class="bi bi-search"></i> Consultar Meu Reparo</a>
                <a href="#services" class="btn btn-outline-light btn-lg mb-2 mb-sm-0"><i class="bi bi-gear-fill"></i> Nossos Serviços</a>
            </div>
        </div>
    </section>

    <section id="services" class="section-padding">
        <div class="container">
            <h2 class="text-center section-title">Nossos Principais Serviços</h2>
            <div class="row">
                {{-- Cards de serviço aqui... --}}
                 <div class="col-md-4 service-card">
                    <div class="icon"><i class="bi bi-display"></i></div>
                    <h4>Troca de Telas e Vidros</h4>
                    <p>Realizamos a troca de telas, display completo e vidro traseiro para Apple e Android.</p>
                </div>
                <div class="col-md-4 service-card">
                    <div class="icon"><i class="bi bi-battery-full"></i></div>
                    <h4>Troca de Baterias</h4>
                    <p>Substituição de baterias para smartphones Apple e Android, restaurando a autonomia do seu aparelho.</p>
                </div>
                <div class="col-md-4 service-card">
                    <div class="icon"><i class="bi bi-motherboard"></i></div>
                    <h4>Reparo Avançado em Placas</h4>
                    <p>Especialistas em reparação de placas lógicas e componentes eletrônicos.</p>
                </div>
                <div class="col-md-4 service-card">
                    <div class="icon"><i class="bi bi-plug-fill"></i></div>
                    <h4>Troca de Componentes</h4>
                    <p>Substituição de conectores de carga, alto-falantes, microfones e outros periféricos.</p>
                </div>
                <div class="col-md-4 service-card">
                    <div class="icon"><i class="bi bi-unlock"></i></div>
                    <h4>Software e Desbloqueios</h4>
                    <p>Serviços de atualização de sistema, remoção de contas e desbloqueios diversos.</p>
                </div>
                <div class="col-md-4 service-card">
                    <div class="icon"><i class="bi bi-shop"></i></div>
                    <h4>Acessórios e Smartphones</h4>
                    <p>Venda de acessórios em geral e também smartphones novos e seminovos.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="section-padding bg-light">
        {{-- Conteúdo da seção sobre nós... --}}
         <div class="container">
            <h2 class="text-center section-title">Sobre a JM Celulares</h2>
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <p class="lead">A JM Celulares é a sua assistência técnica de confiança em Monte Azul e região. Com anos de experiência, oferecemos serviços de reparo rápidos e eficientes, além de uma vasta gama de acessórios para o seu smartphone.</p>
                    <p>Nossa missão é conectar você com o mundo, garantindo que seu aparelho esteja sempre funcionando perfeitamente. Contamos com técnicos qualificados e utilizamos peças de reposição de alta qualidade para assegurar a durabilidade dos nossos serviços.</p>
                    <p><strong>Nossos Valores:</strong> Honestidade, Transparência, Qualidade e Agilidade no Atendimento.</p>
                </div>
                <div class="col-md-6">
                    <img src="https://via.placeholder.com/500x350.png?text=Equipe+JM+Celulares" class="img-fluid rounded shadow" alt="Equipe JM Celulares">
                </div>
            </div>
        </div>
    </section>

    <section id="brands" class="section-padding">
        {{-- Conteúdo da seção de marcas... --}}
         <div class="container">
            <h2 class="text-center section-title">Trabalhamos com as Principais Marcas</h2>
            <div class="d-flex flex-wrap justify-content-center align-items-center">
                <img src="{{ asset('images/marcas/samsung-logo.png') }}" alt="Samsung" style="height: 50px; margin: 15px;">
                <img src="{{ asset('images/marcas/lg-logo.png') }}" alt="LG" style="height: 50px; margin: 15px;">
                <img src="{{ asset('images/marcas/motorola-logo.png') }}" alt="Motorola" style="height: 50px; margin: 15px;">
                <img src="{{ asset('images/marcas/xiaomi-logo.png') }}" alt="Xiaomi" style="height: 50px; margin: 15px;">
                <img src="{{ asset('images/marcas/apple-logo.png') }}" alt="Apple" style="height: 50px; margin: 15px;">
               <i class="bi bi-android2" ></i>
            </div>
        </div>
    </section>

    <section id="contact" class="section-padding bg-light"> {{-- Adicionei bg-light para alternar --}}
        {{-- Conteúdo da seção de contato... --}}
         <div class="container">
            <h2 class="text-center section-title">Entre em Contato</h2>
            <div class="row">
                <div class="col-md-6 mb-4 mb-md-0 contact-info">
                    <h4 style="color: var(--jm-laranja); font-weight:bold;">JM CELULARES</h4>
                    <p><i class="bi bi-geo-alt-fill me-2"></i><strong>Endereço:</strong> Alameda Capitão José Custódio, 130 - Centro, Monte Azul - MG, CEP: 39500-000</p>
                    <p><i class="bi bi-telephone-fill me-2"></i><strong>Telefone:</strong> <a href="tel:+5538992696404" class="text-decoration-none text-dark">(38) 99269-6404</a></p>
                    <p><i class="bi bi-whatsapp me-2 text-success"></i><strong>WhatsApp:</strong> <a href="https://wa.me/5538992696404?text=Olá!%20Gostaria%20de%20mais%20informações." target="_blank" class="text-decoration-none text-dark">(38) 99269-6404</a></p>
                    <p><i class="bi bi-instagram me-2" style="color: #E1306C;"></i><strong>Instagram:</strong> <a href="https://instagram.com/Jmcelulares.mg" target="_blank" class="text-decoration-none text-dark">@Jmcelulares.mg</a></p>
                    <p><i class="bi bi-envelope-fill me-2"></i><strong>Email:</strong> <a href="mailto:contato@jmcelulares.com.br" class="text-decoration-none text-dark">contato@jmcelulares.com.br</a></p>
                    <p><i class="bi bi-clock-fill me-2"></i><strong>Horário de Funcionamento:</strong></p>
                    <ul class="list-unstyled ps-4">
                        <li>Segunda a Sexta: 09:00 - 18:00</li>
                        <li>Sábado: 09:00 - 13:00</li>
                        <li>Domingo: Fechado</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h4>Nossa Localização</h4>
                    <div class="map-responsive shadow">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3829.2198477488627!2d-42.87360008890619!3d-15.165549999999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x75501c0144416a7%3A0x9099210b0a590350!2sAlameda%20Capit%C3%A3o%20Jos%C3%A9%20Cust%C3%B3dio%2C%20130%20-%20Centro%2C%20Monte%20Azul%20-%20MG%2C%2039500-000!5e0!3m2!1spt-BR!2sbr!4v1715655000000!5m2!1spt-BR!2sbr" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

{{-- Se precisar de scripts específicos para a página inicial (raro para uma página estática) --}}

