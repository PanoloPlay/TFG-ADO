<?php require_once '../GENERAL/[General_REQUIRES].php'; ?>
<?php require_once '../GENERAL/[html_START - head_START].php'; ?>

<style>
    .support-page {
        display: grid;
        gap: 28px;
    }

    .support-hero {
        position: relative;
        overflow: hidden;
        padding: 56px 28px;
        border-radius: 4px;
        border: 1px solid var(--color-greyneutral-7);
        background:
            radial-gradient(circle at top, rgba(26, 159, 255, 0.16), transparent 38%),
            linear-gradient(180deg, var(--color-greyneutral-4), var(--color-greyneutral-5));
        box-shadow: 0 14px 35px rgba(0, 0, 0, 0.18);
        text-align: center;
    }

    .support-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
        background-size: 34px 34px;
        opacity: .22;
        pointer-events: none;
    }

    .support-hero__inner {
        position: relative;
        z-index: 1;
        max-width: 820px;
        margin: 0 auto;
    }

    .support-hero h1 {
        margin: 0 0 14px;
        font-size: clamp(2rem, 5vw, 3.6rem);
        line-height: 1.05;
        color: var(--color-slate-12);
    }

    .support-hero p {
        margin: 0 auto;
        max-width: 70ch;
        color: var(--color-greyneutral-11);
        font-size: 1.05rem;
        line-height: 1.7;
    }

    .support-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 18px;
    }

    .support-card {
        grid-column: span 12;
        border-radius: 4px;
        border: 1px solid var(--color-greyneutral-7);
        background: linear-gradient(180deg, var(--color-greyneutral-4), var(--color-greyneutral-5));
        padding: 24px;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
        transition: transform .18s ease, border-color .18s ease, background .18s ease;
    }

    .support-card:hover {
        transform: translateY(-2px);
        border-color: var(--color-greyneutral-8);
        background: linear-gradient(180deg, var(--color-greyneutral-5), var(--color-greyneutral-6));
    }

    .support-card h3 {
        margin: 0 0 14px;
        color: var(--color-slate-12);
        font-size: 1.25rem;
    }

    .support-card p {
        margin: 0 0 12px;
        color: var(--color-greyneutral-11);
        line-height: 1.72;
    }

    .support-card p:last-of-type {
        margin-bottom: 0;
    }

    .support-card__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 18px;
    }

    .btn-modern {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 12px 18px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 700;
        border: 1px solid transparent;
        transition: transform .15s ease, background .15s ease, border-color .15s ease, opacity .15s ease;
    }

    .btn-modern:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .btn-discord {
        color: white;
        background: linear-gradient(180deg, var(--color-blue-9), var(--color-blue-8));
        border-color: rgba(26, 159, 255, 0.25);
    }

    .btn-discord:hover {
        background: linear-gradient(180deg, var(--color-blue-10), var(--color-blue-9));
        color: white;
    }

    .btn-mail {
        color: var(--color-slate-12);
        background: rgba(255, 255, 255, 0.03);
        border-color: var(--color-greyneutral-7);
    }

    .btn-mail:hover {
        background: rgba(255, 255, 255, 0.05);
        color: var(--color-slate-12);
    }

    .btn-dev {
        color: white;
        background: linear-gradient(180deg, var(--color-green-9), var(--color-green-7));
        border-color: rgba(128, 160, 6, 0.25);
    }

    .btn-dev:hover {
        background: linear-gradient(180deg, var(--color-green-10), var(--color-green-9));
        color: white;
    }

    .discord-banner {
        position: relative;
        overflow: hidden;
        border-radius: 4px;
        border: 1px solid var(--color-greyneutral-7);
        background:
            radial-gradient(circle at top right, rgba(26, 159, 255, 0.22), transparent 32%),
            linear-gradient(180deg, var(--color-greyneutral-4), var(--color-greyneutral-2));
        padding: 28px;
        box-shadow: 0 14px 35px rgba(0, 0, 0, 0.16);
    }

    .discord-banner__inner {
        display: grid;
        gap: 14px;
        align-items: center;
    }

    .discord-banner h2 {
        margin: 0;
        color: var(--color-slate-12);
        font-size: 1.7rem;
    }

    .discord-banner p {
        margin: 0;
        color: var(--color-greyneutral-11);
        line-height: 1.72;
        max-width: 75ch;
    }

    .discord-banner__actions {
        margin-top: 8px;
    }

    .discord-big-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 14px 22px;
        border-radius: 4px;
        background: linear-gradient(180deg, var(--color-blue-10), var(--color-blue-8));
        color: white;
        text-decoration: none;
        font-weight: 800;
        border: 1px solid rgba(26, 159, 255, 0.25);
        transition: transform .15s ease, filter .15s ease;
    }

    .discord-big-btn:hover {
        transform: translateY(-1px);
        filter: brightness(1.05);
        color: white;
        text-decoration: none;
    }

    .support-meta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        margin-bottom: 14px;
        border-radius: 4px;
        border: 1px solid rgba(26, 159, 255, 0.18);
        background: rgba(26, 159, 255, 0.08);
        color: var(--color-blue-12);
        font-size: .92rem;
        font-weight: 600;
    }

    @media (min-width: 768px) {
        .support-card {
            grid-column: span 6;
        }

        .support-card:nth-child(3) {
            grid-column: span 12;
        }

        .discord-banner__inner {
            grid-template-columns: 1.4fr auto;
        }
    }

    @media (min-width: 1100px) {
        .support-card:nth-child(1),
        .support-card:nth-child(2) {
            grid-column: span 4;
        }

        .support-card:nth-child(3) {
            grid-column: span 4;
        }
    }
</style>

<?php require_once '../GENERAL/[head_END - body_START - header - main_START].php'; ?>

<section class="support-page">
    <header class="support-hero">
        <div class="support-hero__inner">
            <span class="support-meta">Centro de soporte · TFG-ADO Games</span>
            <h1>Soporte, noticias y comunidad</h1>
            <p>
                Encuentra información sobre nosotros, novedades de la plataforma, ayuda directa
                y acceso rápido para unirte como desarrollador.
            </p>
        </div>
    </header>

    <div class="support-grid">
        <article class="support-card">
            <h3>Sobre nosotros</h3>
            <p>
                En <strong>TFG-ADO Games</strong> acercamos desarrolladores independientes y jugadores
                en un entorno seguro, moderno y fácil de usar.
            </p>
            <p>
                Queremos dar visibilidad a proyectos originales y ofrecer una tienda donde descubrir
                juegos de calidad, noticias y soporte centralizado.
            </p>
        </article>

        <article class="support-card">
            <h3>Noticias y soporte</h3>
            <p>
                Mantente al día con actualizaciones de la plataforma, eventos, mejoras y nuevas funciones.
            </p>
            <p>
                También puedes pedir ayuda o comentar incidencias desde nuestra comunidad de Discord.
            </p>

            <div class="support-card__actions">
                <a href="#"
                   class="btn-modern btn-discord"
                   target="_blank"
                   rel="noopener noreferrer">
                    Unirte a Discord
                </a>

                <a href="mailto:soporte@tfgado.com" class="btn-modern btn-mail">
                    Contactar por email
                </a>
            </div>
        </article>

        <article class="support-card">
            <h3>Unirse como desarrollador</h3>
            <p>
                Si quieres publicar tus juegos en TFG-ADO Games, aquí puedes comenzar tu proceso como desarrollador.
            </p>
            <p>
                Tendrás acceso a herramientas para gestionar tu catálogo, imágenes, descripciones, actualizaciones
                y contenido adicional.
            </p>

            <div class="support-card__actions">
                <a href="../DEV/settings-game.php" class="btn-modern btn-dev">
                    Empezar ahora
                </a>
            </div>
        </article>
    </div>

    <section class="discord-banner">
        <div class="discord-banner__inner">
            <div>
                <h2>Únete a la comunidad de Discord</h2>
                <p>
                    Habla con jugadores y desarrolladores, recibe soporte más rápido, comparte sugerencias
                    y entérate antes que nadie de las novedades de TFG-ADO Games.
                </p>
            </div>

            <div class="discord-banner__actions">
                <a href="https://discord.gg/TU_INVITACION"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="discord-big-btn">
                    Entrar al Discord
                </a>
            </div>
        </div>
    </section>
</section>

<?php require_once '../GENERAL/[main_END - footer].php'; ?>
<?php require_once '../GENERAL/[Page_END].php'; ?>