<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Services\FileSecurityService;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\EditProfile;
use Illuminate\Validation\ValidationException;

class CustomEditProfile extends EditProfile
{
    public static function getLabel(): string
    {
        return __('messages.user.account_settings');
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        Section::make(__('messages.user.your_account_information'))
                            ->columns(4)
                            ->schema([
                                Group::make([
                                    SpatieMediaLibraryFileUpload::make('profile')
                                        ->label(__('messages.user.profile') . ':')
                                        ->validationAttribute(__('messages.user.profile'))
                                        ->disk(config('app.media_disk'))
                                        ->collection(User::PROFILE)
                                        ->image()
                                        ->imagePreviewHeight(150)
                                        ->imageEditor('cropper')
                                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                                        ->rules([
                                            'required',
                                            'image',
                                            'mimes:jpeg,png,gif,webp',
                                            'max:2048', // 2MB max
                                            'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000'
                                        ])
                                        ->afterStateUpdated(function ($state, $set) {
                                            if ($state instanceof \Illuminate\Http\UploadedFile) {
                                                // Validate file content security
                                                if (!FileSecurityService::validateFileContent($state)) {
                                                    $set('profile', null);
                                                    throw ValidationException::withMessages([
                                                        'profile' => 'File contains malicious content and cannot be uploaded.'
                                                    ]);
                                                }
                                                
                                                // Validate image content
                                                if (!FileSecurityService::validateImageContent($state)) {
                                                    $set('profile', null);
                                                    throw ValidationException::withMessages([
                                                        'profile' => 'Invalid image file or dimensions.'
                                                    ]);
                                                }
                                            }
                                        })
                                        ->helperText(__('messages.user.profile_upload_help')),
                                ]),
                                Group::make([
                                    TextInput::make('name')
                                        ->label(__('messages.common.name') . ':')
                                        ->placeholder(__('messages.common.name'))
                                        ->validationAttribute(__('messages.common.name'))
                                        ->required()
                                        ->maxLength(255)
                                        ->autofocus(),
                                    TextInput::make('email')
                                        ->label(__('messages.user.email') . ':')
                                        ->placeholder(__('messages.user.email'))
                                        ->validationAttribute(__('messages.user.email'))
                                        ->email()
                                        ->required()
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true),
                                ])->columnSpan(3)->columns(1),
                            ]),
                    ])
                    ->operation('edit')
                    ->model($this->getUser())
                    ->statePath('data'),
            ),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        /** @var User $user */
        $user = auth()->user();
        if ($user->hasRole('user')) {
            return route('filament.user.pages.dashboard');
        }

        return route('filament.admin.pages.dashboard');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('messages.user.account_settings_updated');
    }
}
