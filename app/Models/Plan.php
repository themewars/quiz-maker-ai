<?php

namespace App\Models;

use App\Enums\PlanFrequency;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'frequency',
        'no_of_quiz',
        'price',
        'trial_days',
        'assign_default',
        'status',
        'currency_id',
        // limits
        'exams_per_month',
        'max_questions_per_exam',
        'max_questions_per_month',
        'max_pdf_pages',
        'max_pdf_upload_mb',
        'max_images_ocr',
        'max_website_tokens',
        // toggles
        'export_pdf',
        'export_word',
        'website_to_exam',
        'pdf_to_exam',
        'ppt_quiz',
        'answer_key',
        'white_label',
        'watermark',
        'priority_support',
        'multi_teacher',
        'share_results',
        'email_participants',
        'display_leaderboard',
        // misc
        'allowed_question_types',
        'badge_text',
        'payment_gateway_plan_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [

        'name' => 'string',
        'frequency' => 'integer',
        'no_of_quiz' => 'integer',
        'price' => 'double',
        'trial_days' => 'integer',
        'assign_default' => 'boolean',
        'status' => 'boolean',
        // limits
        'exams_per_month' => 'integer',
        'max_questions_per_exam' => 'integer',
        'max_questions_per_month' => 'integer',
        'max_pdf_pages' => 'integer',
        'max_pdf_upload_mb' => 'integer',
        'max_images_ocr' => 'integer',
        'max_website_tokens' => 'integer',
        // toggles
        'export_pdf' => 'boolean',
        'export_word' => 'boolean',
        'website_to_exam' => 'boolean',
        'pdf_to_exam' => 'boolean',
        'ppt_quiz' => 'boolean',
        'answer_key' => 'boolean',
        'white_label' => 'boolean',
        'watermark' => 'boolean',
        'priority_support' => 'boolean',
        'multi_teacher' => 'boolean',
        'share_results' => 'boolean',
        'email_participants' => 'boolean',
        'display_leaderboard' => 'boolean',
        // misc
        'allowed_question_types' => 'array',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function prices()
    {
        return $this->hasMany(PlanPrice::class);
    }

    public function getPriceForCurrency($currencyId)
    {
        $planPrice = $this->prices()->where('currency_id', $currencyId)->first();
        return $planPrice ? $planPrice->price : $this->price;
    }

    public function getPaymentGatewayPlanIdForCurrency($currencyId)
    {
        $planPrice = $this->prices()->where('currency_id', $currencyId)->first();
        return $planPrice ? $planPrice->payment_gateway_plan_id : $this->payment_gateway_plan_id;
    }

    public static function getForm()
    {
        return [
            Section::make()->schema([
                TextInput::make('name')
                    ->label(__('messages.common.name') . ':')
                    ->placeholder(__('messages.common.name'))
                    ->validationAttribute(__('messages.common.name'))
                    ->required()
                    ->maxLength(255),
                ToggleButtons::make('frequency')
                    ->label(__('messages.plan.frequency') . ':')
                    ->validationAttribute(__('messages.plan.frequency'))
                    ->inline()
                    ->default(PlanFrequency::MONTHLY)
                    ->options(PlanFrequency::class)
                    ->required(),
                Textarea::make('description')
                    ->label(__('messages.quiz.description') . ':')
                    ->placeholder(__('messages.quiz.description'))
                    ->validationAttribute(__('messages.quiz.description'))
                    ->required(),
                TextInput::make('trial_days')
                    ->label(__('messages.plan.trial_days') . ':')
                    ->placeholder(__('messages.plan.trial_days'))
                    ->validationAttribute(__('messages.plan.trial_days'))
                    ->numeric()
                    ->integer(),
                TextInput::make('no_of_quiz')
                    ->numeric()
                    ->label(__('messages.plan.no_of_quizzes') . ':')
                    ->placeholder(__('messages.plan.no_of_quizzes'))
                    ->validationAttribute(__('messages.plan.no_of_quizzes'))
                    ->minValue(1)
                    ->required(),
                Select::make('currency_id')
                    ->label(__('messages.currency.currency') . ':')
                    ->placeholder(__('messages.currency.currency'))
                    ->validationAttribute(__('messages.currency.currency'))
                    ->options(function () {
                        return Currency::get()->mapWithKeys(function ($currency) {
                            return [$currency->id => $currency->symbol . ' - ' . $currency->name];
                        })->toArray();
                    })
                    ->live()
                    ->searchable()
                    ->required()
                    ->preload(),
                TextInput::make('price')
                    ->label(__('messages.common.price') . ':')
                    ->placeholder(__('messages.common.price'))
                    ->validationAttribute(__('messages.common.price'))
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->prefix(function (Get $get) {
                        return $get('currency_id') ? Currency::find($get('currency_id'))->symbol : '$';
                    }),
                Group::make([
                    Toggle::make('assign_default')
                        ->label(__('messages.plan.assign_default'))
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state, string $operation, ?Model $record) {
                            $default = Plan::where('assign_default', true)->exists();
                            if ($operation === 'edit') {
                                $default = Plan::where('assign_default', true)->where('id', '!=', $record->id)->exists();
                            }
                            if (!$default && !$state) {
                                $set('assign_default', true);
                                Notification::make()
                                    ->title(__('messages.plan.default_plan_cannot_turned_off'))
                                    ->danger()
                                    ->send();
                            }
                        }),
                ])->columns(2),
                Section::make(__('Plan Limits'))->schema([
                    TextInput::make('exams_per_month')->numeric()->label('Exams Per Month')->hint('Set to -1 for unlimited exams'),
                    TextInput::make('max_questions_per_exam')->numeric()->label('Max Questions Per Exam')->hint('Set to -1 for unlimited questions per exam'),
                    TextInput::make('max_questions_per_month')->numeric()->label('Max Questions Per Month')->hint('Set to -1 for unlimited questions per month'),
                    TextInput::make('max_pdf_pages')->numeric()->label('Max PDF Pages Allowed')->hint('Leave empty for unlimited'),
                    TextInput::make('max_pdf_upload_mb')->numeric()->label('Max PDF Upload Size (MB)')->hint('Leave empty for unlimited'),
                    TextInput::make('max_images_ocr')->numeric()->label('Max Images Allowed (OCR)')->hint('Leave empty for unlimited'),
                    TextInput::make('max_website_tokens')->numeric()->label('Max Website Tokens Allowed')->hint('Leave empty for unlimited'),
                ])->columns(3),
                Section::make(__('Plan Features'))->schema([
                    Toggle::make('export_pdf')->label(__('messages.plan.export_pdf')),
                    Toggle::make('export_word')->label(__('messages.plan.export_word')),
                    Toggle::make('website_to_exam')->label('Website to Exam Enabled'),
                    Toggle::make('pdf_to_exam')->label('PDF to Exam Enabled'),
                    Toggle::make('ppt_quiz')->label('PPT Quiz Enabled'),
                    Toggle::make('answer_key')->label('Answer Key Enabled'),
                    Toggle::make('white_label')->label('White Label Enabled'),
                    Toggle::make('watermark')->label('Watermark Enabled'),
                    Toggle::make('priority_support')->label('Priority Support Enabled'),
                    Toggle::make('multi_teacher')->label('Multi Teacher Enabled'),
                    Toggle::make('share_results')->label('Share results with participants'),
                    Toggle::make('email_participants')->label('Email participants'),
                    Toggle::make('display_leaderboard')->label('Display Leaderboard Enabled'),
                ])->columns(3),
                Section::make(__('Question Types'))
                    ->schema([
                        Select::make('allowed_question_types')
                            ->multiple()
                            ->options([
                                'multiple_choice' => 'Multiple Choice Questions',
                                'single_choice' => 'Single Choice Questions',
                                'open_ended' => 'Open Ended Questions',
                                'true_false' => 'True / False Questions',
                            ])
                            ->label('Allowed Question Types')
                            ->hint('Select which question types are allowed for this plan'),
                    ]),
                Section::make(__('Badge & Payment'))
                    ->schema([
                        TextInput::make('badge_text')->label('Badge Text')->placeholder('e.g., Popular, Best Value, Recommended'),
                        TextInput::make('payment_gateway_plan_id')->label('Payment Gateway Plan ID')->placeholder('Enter payment gateway plan ID'),
                    ])->columns(2),
            ])->columns(2)
        ];
    }
}
