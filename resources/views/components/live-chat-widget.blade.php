<!-- Live Chat Widget -->
@if(config('livechat.chat_enabled', true) && config('livechat.tawk_widget_id'))
<script type="text/javascript">
    var Tawk_API = Tawk_API || {};
    var Tawk_LoadStart = new Date();
    
    // Customize chat widget to prevent design conflicts
    Tawk_API.customStyle = {
        zIndex: 999999, // High z-index to stay on top
        visibility: {
            desktop: {
                position: 'br', // bottom right
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
    
    // Prevent chat widget from affecting page layout
    Tawk_API.onLoad = function() {
        // Ensure chat widget doesn't interfere with page design
        var chatWidget = document.querySelector('#tawk-widget');
        if (chatWidget) {
            chatWidget.style.position = 'fixed';
            chatWidget.style.zIndex = '999999';
        }
    };
    
    // Set admin online status
    @if(config('livechat.admin_online_status', true))
    Tawk_API.setAttributes({
        'name': '{{ auth()->user()->name ?? "Admin" }}',
        'email': '{{ auth()->user()->email ?? "admin@examgenerator.ai" }}',
        'hash': '{{ md5(auth()->user()->email ?? "admin@examgenerator.ai") }}'
    });
    @endif
    
    (function() {
        var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = 'https://embed.tawk.to/{{ config("livechat.tawk_widget_id") }}/default';
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
    })();
</script>

<!-- Additional CSS to prevent design conflicts -->
<style>
    /* Ensure chat widget doesn't interfere with page layout */
    #tawk-widget {
        position: fixed !important;
        z-index: 999999 !important;
        bottom: 20px !important;
        right: 20px !important;
    }
    
    /* Prevent chat widget from affecting page scrolling - only for chat widget */
    #tawk-widget * {
        box-sizing: border-box !important;
    }
    
    /* Ensure main content is not affected */
    main {
        position: relative !important;
        z-index: 1 !important;
    }
    
    /* Prevent chat widget from affecting site icons and images */
    .hero img,
    .feature img,
    .icon,
    svg {
        max-width: 100% !important;
        height: auto !important;
    }
</style>
@else
<!-- Fallback: Direct Tawk.to Integration -->
<script type="text/javascript">
    var Tawk_API = Tawk_API || {};
    var Tawk_LoadStart = new Date();
    
    // Customize chat widget to prevent design conflicts
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
    
    (function() {
        var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = 'https://embed.tawk.to/68ea459b7ab386194e5c8db9/1j79hvq2c';
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
    })();
</script>

<!-- Additional CSS to prevent design conflicts -->
<style>
    #tawk-widget {
        position: fixed !important;
        z-index: 999999 !important;
        bottom: 20px !important;
        right: 20px !important;
    }
    
    #tawk-widget * {
        box-sizing: border-box !important;
    }
    
    main {
        position: relative !important;
        z-index: 1 !important;
    }
    
    /* Prevent chat widget from affecting site icons and images */
    .hero img,
    .feature img,
    .icon,
    svg {
        max-width: 100% !important;
        height: auto !important;
    }
</style>
@endif
