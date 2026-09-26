import './bootstrap';

// Font lokal (self-host) agar aplikasi tetap tampil penuh di jaringan intranet
// tanpa koneksi internet.
import '@fontsource/plus-jakarta-sans/400.css';
import '@fontsource/plus-jakarta-sans/500.css';
import '@fontsource/plus-jakarta-sans/600.css';
import '@fontsource/plus-jakarta-sans/700.css';
import '@fontsource/plus-jakarta-sans/800.css';
import '@fontsource/roboto/400.css';
import '@fontsource/roboto/500.css';
import '@fontsource/roboto/700.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
