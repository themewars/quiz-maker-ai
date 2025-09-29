<?php

namespace App\Filament\User\Pages;

use App\Models\User;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Enums\UserSidebar;
use App\Models\UserSetting;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\Quiz;

class QuizSetting extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.user.pages.quiz-setting';

    protected static ?int $navigationSort = UserSidebar::QUIZ_SETTINGS->value;

    public ?array $data = [];
    public static function getNavigationLabel(): string
    {
        return __('messages.setting.quiz_settings');
    }

    public function getTitle(): string
    {
        return __('messages.setting.quiz_settings');
    }

    public function mount()
    {
        $this->form->fill([
            'hide_participant_email_in_leaderboard' => getUserSettings('hide_participant_email_in_leaderboard') ?? 1,
            'default_language' => getUserSettings('default_language') ?? 'en',
            'default_question_type' => getUserSettings('default_question_type') ?? 0,
            'default_difficulty' => getUserSettings('default_difficulty') ?? 0,
            'default_questions_count' => getUserSettings('default_questions_count') ?? 10,
            'default_paper' => getUserSettings('default_paper') ?? 'A4',
            'default_orientation' => getUserSettings('default_orientation') ?? 'portrait',
            'include_description_default' => getUserSettings('include_description_default') ?? 1,
            'include_answers_default' => getUserSettings('include_answers_default') ?? 1,
            'mark_correct_default' => getUserSettings('mark_correct_default') ?? 1,
            'public_default' => getUserSettings('public_default') ?? 0,
            'show_on_home_default' => getUserSettings('show_on_home_default') ?? 0,
            'enable_public_share_link' => getUserSettings('enable_public_share_link') ?? 1,
            'show_qr_on_export' => getUserSettings('show_qr_on_export') ?? 0,
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
                Section::make('Defaults for New Exams')
                    ->schema([
                        Select::make('default_language')
                            ->label(__('messages.home.language') . ':')
                            ->options(getAllLanguages())
                            ->native(false)
                            ->searchable()
                            ->required(),
                        Select::make('default_question_type')
                            ->label(__('messages.quiz.question_type') . ':')
                            ->options(Quiz::getQuizTypeOptions())
                            ->native(false)
                            ->required(),
                        Select::make('default_difficulty')
                            ->label(__('messages.quiz.difficulty') . ':')
                            ->options(Quiz::getDiffLevelOptions())
                            ->native(false)
                            ->required(),
                        TextInput::make('default_questions_count')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->required()
                            ->label(__('messages.quiz.num_of_questions') . ':'),
                        ToggleButtons::make('public_default')
                            ->label(__('messages.quiz.make_public') . ':')
                            ->options(['1' => __('messages.setting.yes'), '0' => __('messages.setting.no')])
                            ->inline()
                            ->required(),
                        ToggleButtons::make('show_on_home_default')
                            ->label(__('messages.quiz.show_home') . ':')
                            ->options(['1' => __('messages.setting.yes'), '0' => __('messages.setting.no')])
                            ->inline()
                            ->required(),
                    ])->columns(2),

                Section::make('Export Defaults')
                    ->schema([
                        Select::make('default_paper')
                            ->label('Paper size:')
                            ->options(['A4' => 'A4', 'A3' => 'A3', 'Letter' => 'Letter', 'Legal' => 'Legal'])
                            ->native(false)
                            ->required(),
                        Select::make('default_orientation')
                            ->label('Orientation:')
                            ->options(['portrait' => 'Portrait', 'landscape' => 'Landscape'])
                            ->native(false)
                            ->required(),
                        ToggleButtons::make('include_description_default')
                            ->label('Include description:')
                            ->options(['1' => __('messages.setting.yes'), '0' => __('messages.setting.no')])
                            ->inline()
                            ->required(),
                        ToggleButtons::make('include_answers_default')
                            ->label('Include answers:')
                            ->options(['1' => __('messages.setting.yes'), '0' => __('messages.setting.no')])
                            ->inline()
                            ->required(),
                        ToggleButtons::make('mark_correct_default')
                            ->label('Mark correct answers:')
                            ->options(['1' => __('messages.setting.yes'), '0' => __('messages.setting.no')])
                            ->inline()
                            ->required(),
                        ToggleButtons::make('show_qr_on_export')
                            ->label('Show QR code on export footer:')
                            ->options(['1' => __('messages.setting.yes'), '0' => __('messages.setting.no')])
                            ->inline()
                            ->required(),
                    ])->columns(2),

                Section::make('Sharing')
                    ->schema([
                        ToggleButtons::make('enable_public_share_link')
                            ->label('Enable public share link by default:')
                            ->options(['1' => __('messages.setting.yes'), '0' => __('messages.setting.no')])
                            ->inline()
                            ->required(),
                    ]),

                ToggleButtons::make('hide_participant_email_in_leaderboard')
                    ->label(__('messages.setting.hide_participant_email_in_leaderboard') . ':')
                    ->options([
                        '1' => __('messages.setting.show'),
                        '0' => __('messages.setting.hide'),
                    ])
                    ->inline()
                    ->required(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        try {
            foreach ($data as $key => $value) {
                $setting = UserSetting::where('key', $key)
                    ->where('user_id', auth()->id())
                    ->first();
                if ($setting) {
                    $setting->update(['value' => $value]);
                } else {
                    UserSetting::create([
                        'user_id' => auth()->id(),
                        'key' => $key,
                        'value' => $value,
                    ]);
                }
            }

            Notification::make()
                ->success()
                ->title(__('messages.setting.quiz_settings_updated_success'))
                ->send();
        } catch (Exception $exception) {
            Notification::make()
                ->danger()
                ->title($exception->getMessage())
                ->send();
        }
    }
}
