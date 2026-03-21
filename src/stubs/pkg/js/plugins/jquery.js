// Load jQuery once and expose it globally for Blade scripts and plugins (Select2, etc.).
import jquery from "jquery";

window.jQuery = jquery;
window.$ = jquery;
