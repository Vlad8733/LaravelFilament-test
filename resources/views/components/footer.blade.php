<!-- Footer -->
<footer class="site-footer">
    <div class="footer-container">
        <!-- Main Footer Content -->
        <div class="footer-main">
            <!-- Brand Column -->
            <div class="footer-brand">
                <a href="{{ route('home') }}" class="footer-logo">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="32" height="32">
                        <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                    </svg>
                    <span>ShopLy</span>
                </a>
                <p class="footer-tagline">{{ __('footer.tagline') }}</p>
                <div class="footer-social">
                    <a href="#" class="social-link" title="Facebook">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                            <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z"/>
                        </svg>
                    </a>
                    <a href="#" class="social-link" title="Instagram">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                            <path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 0 1 1.25 1.25A1.25 1.25 0 0 1 17.25 8 1.25 1.25 0 0 1 16 6.75a1.25 1.25 0 0 1 1.25-1.25M12 7a5 5 0 0 1 5 5 5 5 0 0 1-5 5 5 5 0 0 1-5-5 5 5 0 0 1 5-5m0 2a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0 3-3 3 3 0 0 0-3-3z"/>
                        </svg>
                    </a>
                    <a href="#" class="social-link" title="Twitter">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                    </a>
                    <a href="#" class="social-link" title="YouTube">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                            <path d="M10 15l5.19-3L10 9v6m11.56-7.83c.13.47.22 1.1.28 1.9.07.8.1 1.49.1 2.09L22 12c0 2.19-.16 3.8-.44 4.83-.25.9-.83 1.48-1.73 1.73-.47.13-1.33.22-2.65.28-1.3.07-2.49.1-3.59.1L12 19c-4.19 0-6.8-.16-7.83-.44-.9-.25-1.48-.83-1.73-1.73-.13-.47-.22-1.1-.28-1.9-.07-.8-.1-1.49-.1-2.09L2 12c0-2.19.16-3.8.44-4.83.25-.9.83-1.48 1.73-1.73.47-.13 1.33-.22 2.65-.28 1.3-.07 2.49-.1 3.59-.1L12 5c4.19 0 6.8.16 7.83.44.9.25 1.48.83 1.73 1.73z"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-column">
                <h4>{{ __('footer.quick_links') }}</h4>
                <ul>
                    <li><a href="{{ route('products.index') }}">{{ __('footer.all_products') }}</a></li>
                    <li><a href="{{ route('products.index') }}">{{ __('footer.categories') }}</a></li>
                    <li><a href="{{ route('pages.about') }}">{{ __('footer.about_us') }}</a></li>
                    <li><a href="{{ route('pages.contact') }}">{{ __('footer.contact') }}</a></li>
                    <li><a href="{{ route('pages.faq') }}">{{ __('footer.faq') }}</a></li>
                </ul>
            </div>

            <!-- Customer Service -->
            <div class="footer-column">
                <h4>{{ __('footer.customer_service') }}</h4>
                <ul>
                    <li><a href="{{ route('orders.tracking.search') }}">{{ __('footer.order_tracking') }}</a></li>
                    @auth
                        <li><a href="{{ route('refunds.index') }}">{{ __('footer.refunds') }}</a></li>
                        <li><a href="{{ route('tickets.index') }}">{{ __('footer.support') }}</a></li>
                    @else
                        <li><a href="{{ route('login') }}">{{ __('footer.support') }}</a></li>
                    @endauth
                    <li><a href="{{ route('pages.faq') }}">{{ __('footer.help_center') }}</a></li>
                </ul>
            </div>

            <!-- My Account -->
            <div class="footer-column">
                <h4>{{ __('footer.my_account') }}</h4>
                <ul>
                    @auth
                        <li><a href="{{ route('profile.edit') }}">{{ __('footer.profile') }}</a></li>
                        <li><a href="{{ route('wishlist.index') }}">{{ __('footer.wishlist') }}</a></li>
                        <li><a href="{{ route('cart.index') }}">{{ __('footer.cart') }}</a></li>
                        <li><a href="{{ route('settings.index') }}">{{ __('footer.settings') }}</a></li>
                    @else
                        <li><a href="{{ route('login') }}">{{ __('footer.sign_in') }}</a></li>
                        <li><a href="{{ route('register') }}">{{ __('footer.register') }}</a></li>
                    @endauth
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-column footer-contact">
                <h4>{{ __('footer.contact_info') }}</h4>
                <ul class="contact-list">
                    <li>
                        <svg viewBox="0 -960 960 960" fill="currentColor" width="18" height="18">
                            <path d="M480-480q33 0 56.5-23.5T560-560q0-33-23.5-56.5T480-640q-33 0-56.5 23.5T400-560q0 33 23.5 56.5T480-480Zm0 400Q319-217 239.5-334.5T160-552q0-150 96.5-239T480-880q127 0 223.5 89T800-552q0 100-79.5 217.5T480-80Z"/>
                        </svg>
                        <span>123 Commerce Street<br>Riga, LV-1050</span>
                    </li>
                    <li>
                        <svg viewBox="0 -960 960 960" fill="currentColor" width="18" height="18">
                            <path d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H160Zm320-280L160-640v400h640v-400L480-440Zm0-80 320-200H160l320 200ZM160-640v-80 480-400Z"/>
                        </svg>
                        <a href="mailto:support@shop.com">support@shop.com</a>
                    </li>
                    <li>
                        <svg viewBox="0 -960 960 960" fill="currentColor" width="18" height="18">
                            <path d="M798-120q-125 0-247-54.5T329-329Q229-429 174.5-551T120-798q0-18 12-30t30-12h162q14 0 25 9.5t13 22.5l26 140q2 16-1 27t-11 19l-97 98q20 37 47.5 71.5T387-386q31 31 65 57.5t72 48.5l94-94q9-9 23.5-13.5T670-390l138 28q14 4 23 14.5t9 23.5v162q0 18-12 30t-30 12Z"/>
                        </svg>
                        <a href="tel:+37120000000">+371 2000 0000</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-bottom-left">
                <p>&copy; {{ date('Y') }} ShopLy. {{ __('footer.all_rights') }}</p>
            </div>
            <div class="footer-bottom-right">
                <a href="{{ route('pages.privacy') }}">{{ __('footer.privacy_policy') }}</a>
                <a href="{{ route('pages.terms') }}">{{ __('footer.terms') }}</a>
                <a href="{{ route('pages.cookies') }}">{{ __('footer.cookies') }}</a>
            </div>
        </div>
    </div>
</footer>
