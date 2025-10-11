<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Live Chat Configuration -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Live Chat Configuration</h3>
            
            <form wire:submit="saveSettings" class="space-y-4">
                <!-- Tawk.to Widget ID -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tawk.to Widget ID
                    </label>
                    <input 
                        type="text" 
                        wire:model="tawkWidgetId"
                        placeholder="Enter your Tawk.to Widget ID"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p class="text-sm text-gray-500 mt-1">
                        Get your Widget ID from <a href="https://www.tawk.to" target="_blank" class="text-blue-600">tawk.to</a>
                    </p>
                </div>
                
                <!-- Chat Enabled -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        wire:model="chatEnabled"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label class="ml-2 text-sm text-gray-700">
                        Enable Live Chat
                    </label>
                </div>
                
                <!-- Admin Online Status -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        wire:model="adminOnlineStatus"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label class="ml-2 text-sm text-gray-700">
                        Show Admin Online Status
                    </label>
                </div>
                
                <!-- Save Button -->
                <div class="pt-4">
                    <button 
                        type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        Save Settings
                    </button>
                </div>
            </form>
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
