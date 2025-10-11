<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Live Chat Configuration Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Live Chat Configuration</h3>
            
            {{ $this->form }}
        </div>
        
        <!-- Integration Instructions -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Integration Instructions</h3>
            <div class="space-y-3 text-sm text-gray-700">
                <p><strong>1.</strong> Sign up for free at <a href="https://www.tawk.to" target="_blank" class="text-blue-600">tawk.to</a></p>
                <p><strong>2.</strong> Get your Widget ID from the dashboard</p>
                <p><strong>3.</strong> Enter the Widget ID above and save</p>
                <p><strong>4.</strong> The chat widget will appear on your website</p>
                <p><strong>5.</strong> Admin can manage chats from Tawk.to dashboard</p>
            </div>
        </div>
        
        <!-- Features -->
        <div class="bg-green-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-green-800">Free Features</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-green-700">
                <div>✅ Unlimited chats</div>
                <div>✅ Admin online status</div>
                <div>✅ Mobile responsive</div>
                <div>✅ File sharing</div>
                <div>✅ Multi-language</div>
                <div>✅ Chat history</div>
                <div>✅ Email notifications</div>
                <div>✅ Custom branding</div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
