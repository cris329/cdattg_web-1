<footer class="app-footer" style="
    border-top: 1px solid #e5e7eb !important;
    background: #ffffff !important;
    font-size: 12.5px !important;
    color: #4b5563 !important;
    margin-top: auto !important;
    padding: 0 !important;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
    line-height: 1.5 !important;
    box-sizing: border-box !important;
    display: block !important;
    width: 100% !important;
">
    <div class="footer-container" style="
        max-width: 1400px !important;
        margin: 0 auto !important;
        padding: 20px 24px !important;
        display: grid !important;
        grid-template-columns: 1fr 1fr 1fr !important;
        align-items: center !important;
        gap: 16px !important;
        box-sizing: border-box !important;
    ">
        
        <!-- Col 1: Marca -->
        <div class="footer-brand" style="
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            box-sizing: border-box !important;
        ">
            <img src="{{ asset('vendor/adminlte/dist/img/LogoSena.png') }}" alt="SENA" style="
                height: 36px !important;
                width: auto !important;
                object-fit: contain !important;
                display: block !important;
            " />
            <div style="
                display: flex !important;
                flex-direction: column !important;
                gap: 2px !important;
            ">
                <strong style="
                    display: block !important;
                    font-size: 14px !important;
                    color: #1f2937 !important;
                    font-weight: 600 !important;
                    margin-bottom: 2px !important;
                    text-transform: none !important;
                ">SENA Regional Guaviare</strong>
                <span style="
                    font-size: 12px !important;
                    color: #6b7280 !important;
                    font-weight: 400 !important;
                    text-transform: none !important;
                ">Industria y Tecnología</span>
            </div>
        </div>

        <!-- Col 2: Social -->
        <div class="footer-social" style="
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            gap: 8px !important;
            box-sizing: border-box !important;
        ">
            <span style="
                display: block !important;
                font-weight: 500 !important;
                color: #374151 !important;
                font-size: 12px !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
                margin-bottom: 0 !important;
            ">Conéctate con nosotros</span>
            <div class="social-links" style="
                display: flex !important;
                gap: 12px !important;
                flex-wrap: nowrap !important;
            ">
                <a href="https://www.facebook.com/SENA" target="_blank" rel="noopener" style="
                    color: #2563eb !important;
                    text-decoration: none !important;
                    font-size: 16px !important;
                    width: 32px !important;
                    height: 32px !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    border-radius: 6px !important;
                    transition: all 0.2s ease !important;
                    background: rgba(37, 99, 235, 0.05) !important;
                    border: 1px solid rgba(37, 99, 235, 0.1) !important;
                    box-sizing: border-box !important;
                    cursor: pointer !important;
                ">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com/SENAComunica" target="_blank" rel="noopener" style="
                    color: #2563eb !important;
                    text-decoration: none !important;
                    font-size: 16px !important;
                    width: 32px !important;
                    height: 32px !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    border-radius: 6px !important;
                    transition: all 0.2s ease !important;
                    background: rgba(37, 99, 235, 0.05) !important;
                    border: 1px solid rgba(37, 99, 235, 0.1) !important;
                    box-sizing: border-box !important;
                    cursor: pointer !important;
                ">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://www.linkedin.com/school/senaoficial" target="_blank" rel="noopener" style="
                    color: #2563eb !important;
                    text-decoration: none !important;
                    font-size: 16px !important;
                    width: 32px !important;
                    height: 32px !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    border-radius: 6px !important;
                    transition: all 0.2s ease !important;
                    background: rgba(37, 99, 235, 0.05) !important;
                    border: 1px solid rgba(37, 99, 235, 0.1) !important;
                    box-sizing: border-box !important;
                    cursor: pointer !important;
                ">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            </div>
        </div>

        <!-- Col 3: Meta -->
        <div class="footer-meta" style="
            text-align: right !important;
            font-size: 12px !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 4px !important;
            box-sizing: border-box !important;
        ">
            <span style="
                display: block !important;
                color: #374151 !important;
                font-weight: 500 !important;
                margin: 0 !important;
                line-height: 1.4 !important;
            ">Versión 3.2.0</span>
            <span style="
                display: block !important;
                color: #6b7280 !important;
                font-weight: 400 !important;
                margin: 0 !important;
                line-height: 1.4 !important;
            ">info@dataguaviare.com.co</span>
        </div>
        
    </div>

    <div class="footer-bottom" style="
        border-top: 1px solid #f1f5f9 !important;
        padding: 12px 24px !important;
        text-align: center !important;
        font-size: 12px !important;
        color: #9ca3af !important;
        background: #fafbfc !important;
        margin: 0 !important;
        font-weight: 400 !important;
        box-sizing: border-box !important;
        width: 100% !important;
        display: block !important;
        clear: both !important;
    ">
        © {{ now()->format('Y') }} SENA · Plataforma Académica
    </div>
</footer>

<!-- Global Modals - Cargado UNA SOLA VEZ -->
@include('layouts.partials.global-modals')
