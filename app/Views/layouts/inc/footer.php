<footer class="anitala-footer">
    <div class="footer-wrapper">
        <div class="footer-content">
        <div class="footer-bottom">
            <p class="footer-copyright">All rights reserved &copy; 2026 | Agri Savers G Management Information System | by CSPC Interns - Batch 2.</p>
        </div>
    </div>
</footer>

<style>
    /* AniTala Footer Styles */
    .anitala-footer {
        background: white;
        border-top: 1px solid #e8f5e9;
        padding: 1.5rem 0 0.75rem 0;
        margin-top: 1.5rem;
        font-size: 0.9rem;
    }

    .footer-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.25rem;
    }

    .footer-section {
        display: flex;
        flex-direction: column;
    }

    .footer-section__title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #2d7d32;
        margin: 0 0 0.5rem 0;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .footer-section__list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-section__list li {
        margin-bottom: 0.4rem;
    }

    .footer-link {
        color: #6c7a89;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
    }

    .footer-link:hover {
        color: #4caf50;
        padding-left: 0.25rem;
    }

    /* Social Links */
    .footer-social {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .footer-social__link {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(76, 175, 80, 0.1);
        color: #2d7d32;
        border-radius: 8px;
        transition: all 0.3s ease;
        border: 1px solid rgba(76, 175, 80, 0.2);
    }

    .footer-social__link:hover {
        background: #4caf50;
        color: white;
        border-color: #4caf50;
        transform: translateY(-2px);
    }

    .footer-social__link i {
        font-size: 1.25rem;
    }

    /* Footer Bottom */
    .footer-bottom {
        border-top: 1px solid #e8f5e9;
        padding-top: 0.75rem;
        text-align: center;
    }

    .footer-copyright {
        color: #95a5a6;
        font-size: 0.8rem;
        margin: 0;
        line-height: 1.3;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .anitala-footer {
            padding: 1rem 0 0.5rem 0;
            margin-top: 1rem;
        }

        .footer-wrapper {
            padding: 0 1rem;
        }

        .footer-content {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .footer-section__title {
            font-size: 0.8rem;
            margin: 0 0 0.4rem 0;
        }

        .footer-section__list li {
            margin-bottom: 0.3rem;
        }

        .footer-link {
            font-size: 0.85rem;
        }

        .footer-copyright {
            font-size: 0.75rem;
        }
    }

    @media (max-width: 480px) {
        .anitala-footer {
            padding: 0.75rem 0 0.5rem 0;
            margin-top: 0.75rem;
        }

        .footer-content {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .footer-section {
            text-align: center;
        }

        .footer-bottom {
            padding-top: 0.5rem;
        }

        .footer-copyright {
            font-size: 0.7rem;
        }
    }
</style>