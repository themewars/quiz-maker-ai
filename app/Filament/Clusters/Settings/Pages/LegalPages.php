<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Enums\AdminSettingSidebar;
use App\Filament\Clusters\Settings;
use App\Models\Setting;
use Exception;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class LegalPages extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.clusters.settings.pages.legal-pages';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = AdminSettingSidebar::LEGAL_PAGES->value;

    public function mount()
    {
        $seeting = Setting::first();

        $this->form->fill([
            'terms_and_condition' => $seeting->terms_and_condition,
            'privacy_policy' => $seeting->privacy_policy,
            'cookie_policy' => $seeting->cookie_policy,
            'custom_legal_pages' => is_array($seeting->custom_legal_pages)
                ? $seeting->custom_legal_pages
                : (empty($seeting->custom_legal_pages) ? [] : (json_decode($seeting->custom_legal_pages, true) ?? [])),
        ]);
    }

    public function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->submit('save'),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                RichEditor::make('terms_and_condition')
                    ->label(__('messages.home.terms_and_conditions'))
                    ->placeholder(__('messages.home.terms_and_conditions')),
                RichEditor::make('privacy_policy')
                    ->label(__('messages.home.privacy_policy'))
                    ->placeholder(__('messages.home.privacy_policy')),
                RichEditor::make('cookie_policy')
                    ->label(__('messages.home.cookie_policy'))
                    ->placeholder(__('messages.home.cookie_policy')),
                Repeater::make('custom_legal_pages')
                    ->label('Additional Pages')
                    ->addActionLabel('Add page')
                    ->schema([
                        TextInput::make('title')->label('Page title')->required(),
                        TextInput::make('slug')->label('Slug')->helperText('Unique URL slug, e.g., refund-policy')->required(),
                        RichEditor::make('content')->label('Content')->required(),
                    ])
                    ->default([])
                    ->grid(1),
            ])
            ->columns(1)
            ->statePath('data');
    }

    public function save()
    {
        try {
            $data = $this->form->getState();
            Setting::first()->update($data);
            Notification::make()
                ->success()
                ->title(__('messages.setting.legal_pages_updated_success'))
                ->send();
        } catch (Exception $exception) {
            Notification::make()
                ->danger()
                ->title($exception->getMessage())
                ->send();
        }
    }
}
