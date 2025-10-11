<!-- Live Chat Widget -->
<script type="text/javascript">
    var Tawk_API = Tawk_API || {};
    var Tawk_LoadStart = new Date();
    
    // Customize chat widget
    Tawk_API.customStyle = {
        zIndex: 999999,
        visibility: {
            desktop: {
                position: 'br',
                xOffset: 20,
                yOffset: 20
            },
            mobile: {
                position: 'br',
                xOffset: 10,
                yOffset: 10
            }
        }
    };
    
    // Set admin online status
    @if(auth()->check())
    Tawk_API.setAttributes({
        'name': '{{ auth()->user()->name }}',
        'email': '{{ auth()->user()->email }}',
        'hash': '{{ md5(auth()->user()->email) }}'
    });
    @endif
    
    (function() {
        var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = 'https://embed.tawk.to/68ea459b7ab386194e5c8db9/1j79hvq2c';
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
    })();
</script>

<!-- CSS to prevent conflicts -->
<style>
    #tawk-widget {
        position: fixed !important;
        z-index: 999999 !important;
        bottom: 20px !important;
        right: 20px !important;
    }
    
    /* Ensure chat widget doesn't interfere with page layout */
    #tawk-widget * {
        box-sizing: border-box !important;
    }
    
    /* Prevent conflicts with site elements */
    .hero, .features, .about, .pricing {
        position: relative !important;
        z-index: 1 !important;
    }
</style>
