<!-- Live Chat Widget -->
@if(config('livechat.chat_enabled', true) && config('livechat.tawk_widget_id'))
<script type="text/javascript">
    var Tawk_API = Tawk_API || {};
    var Tawk_LoadStart = new Date();
    
    // Customize chat widget
    Tawk_API.customStyle = {
        zIndex: 1000,
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
@else
<!-- Fallback: Direct Tawk.to Integration -->
<script type="text/javascript">
    var Tawk_API = Tawk_API || {};
    var Tawk_LoadStart = new Date();
    
    (function() {
        var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = 'https://embed.tawk.to/68ea459b7ab386194e5c8db9/1j79hvq2c';
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
    })();
</script>
@endif
