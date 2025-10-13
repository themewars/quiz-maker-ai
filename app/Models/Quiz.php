<?php

namespace App\Models;

use Carbon\Carbon;
use Filament\Forms\Get;
use Spatie\MediaLibrary\HasMedia;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Date;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\CheckboxList;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Placeholder;

class Quiz extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'quizzes';

    public const QUIZ_PATH = 'quiz_document';

    protected $fillable = [
        'title',
        'quiz_description',
        'user_id',
        'status',
        'type',
        'category_id',
        'diff_level',
        'quiz_type',
        'max_questions',
        'unique_code',
        'view_count',
        'time_configuration',
        'time',
        'time_type',
        'quiz_expiry_date',
        'is_show_home',
        'is_public',
    ];

    protected $casts = [
        'title' => 'string',
        'quiz_description' => 'string',
        'user_id' => 'integer',
        'status' => 'boolean',
        'type' => 'integer',
        'diff_level' => 'integer',
        'quiz_type' => 'integer',
        'max_questions' => 'integer',
        'unique_code' => 'string',
        'view_count' => 'integer',
        'is_public' => 'boolean',
    ];

    const TEXT_TYPE = 1;

    const SUBJECT_TYPE = 2;

    const URL_TYPE = 3;

    const UPLOAD_TYPE = 4;

    const TIME_OVER_QUESTION = 1;

    const TIME_OVER_QUIZ = 2;

    const QUIZ_INPUT_TYPE = [
        self::TEXT_TYPE => 'Text',
        self::SUBJECT_TYPE => 'Subject',
        self::URL_TYPE => 'URL',
        self::UPLOAD_TYPE => 'Upload File',
    ];

    const OPEN_AI = 1;

    const GEMINI_AI = 2;

    const AI_TYPES = [
        self::OPEN_AI => 'Open AI',
        self::GEMINI_AI => 'Gemini AI',
    ];

    protected $appends = [
        'quiz_document',
        'question_count',
    ];

    public function getQuizDocumentAttribute()
    {
        return $this->getFirstMediaUrl(self::QUIZ_PATH);
    }

    const MULTIPLE_CHOICE = 0;
    const SINGLE_CHOICE = 1;
    const TRUE_FALSE = 2;
    const OPEN_ENDED = 3;
    const QUIZ_TYPE = [
        self::MULTIPLE_CHOICE => 'Multiple Choices',
        self::SINGLE_CHOICE => 'Single Choice',
        self::TRUE_FALSE => 'True / False',
        self::OPEN_ENDED => 'Open Ended',
    ];

    public static function getQuizTypeOptions()
    {
        return [
            0 => __('messages.home.multiple_choice'),
            1 => __('messages.home.single_choice'),
            2 => __('True / False'),
            3 => __('Open Ended'),
        ];
    }

    const DIFF_LEVEL = [
        0 => 'Basic',
        1 => 'Intermediate',
        2 => 'Advanced',
    ];

    public static function getDiffLevelOptions()
    {
        return [
            0 => __('messages.quiz.basic'),
            1 => __('messages.quiz.intermediate'),
            2 => __('messages.quiz.advanced'),
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    protected function getQuestionCountAttribute()
    {
        return $this->questions()->count();
    }

    public function quizUser()
    {
        return $this->hasMany(UserQuiz::class);
    }

    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'quiz_collaborators')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'quiz_teachers', 'quiz_id', 'user_id')->withTimestamps();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getTopicsAttribute()
    {
        $topics = [];
        
        // Add category as first topic
        if ($this->category) {
            $topics[] = $this->category->name;
        }
        
        // Generate topics based on quiz title and description
        $content = strtolower($this->title . ' ' . $this->quiz_description);
        
        // Math topics
        if (strpos($content, 'math') !== false || strpos($content, 'mathematics') !== false || strpos($content, 'algebra') !== false) {
            $topics = array_merge($topics, ['Algebra', 'Geometry', 'Calculus', 'Statistics']);
        }
        // Science topics
        elseif (strpos($content, 'science') !== false || strpos($content, 'physics') !== false || strpos($content, 'chemistry') !== false || strpos($content, 'biology') !== false) {
            $topics = array_merge($topics, ['Physics', 'Chemistry', 'Biology', 'Earth Science']);
        }
        // Biology topics
        elseif (strpos($content, 'biology') !== false || strpos($content, 'cell') !== false || strpos($content, 'genetics') !== false) {
            $topics = array_merge($topics, ['Cell Biology', 'Genetics', 'Evolution', 'Ecology']);
        }
        // History topics
        elseif (strpos($content, 'history') !== false || strpos($content, 'ancient') !== false || strpos($content, 'world') !== false) {
            $topics = array_merge($topics, ['Ancient History', 'World Wars', 'Modern History', 'Geography']);
        }
        // English topics
        elseif (strpos($content, 'english') !== false || strpos($content, 'literature') !== false || strpos($content, 'grammar') !== false) {
            $topics = array_merge($topics, ['Grammar', 'Literature', 'Comprehension', 'Writing']);
        }
        // Computer Science topics
        elseif (strpos($content, 'computer') !== false || strpos($content, 'programming') !== false || strpos($content, 'coding') !== false) {
            $topics = array_merge($topics, ['Programming', 'Data Structures', 'Algorithms', 'Web Development']);
        }
        // Default topics based on category
        else {
            if ($this->category) {
                $categoryName = strtolower($this->category->name);
                if (strpos($categoryName, 'math') !== false) {
                    $topics = array_merge($topics, ['Algebra', 'Geometry', 'Calculus', 'Statistics']);
                } elseif (strpos($categoryName, 'science') !== false) {
                    $topics = array_merge($topics, ['Physics', 'Chemistry', 'Biology', 'Earth Science']);
                } elseif (strpos($categoryName, 'english') !== false) {
                    $topics = array_merge($topics, ['Grammar', 'Literature', 'Comprehension', 'Writing']);
                } else {
                    $topics = array_merge($topics, ['Fundamentals', 'Advanced Concepts', 'Practice Questions', 'Review']);
                }
            } else {
                $topics = array_merge($topics, ['General Knowledge', 'Practice Questions', 'Review', 'Assessment']);
            }
        }
        
        // Remove duplicates and limit to 4 topics
        $topics = array_unique($topics);
        return array_slice($topics, 0, 4);
    }

    public static function  getForm(): array
    {
        return [
            Section::make()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Grid::make(1)
                                ->schema([
                                    Group::make([
                                        TextInput::make('title')
                                            ->label(__('messages.quiz.title') . ':')
                                            ->placeholder(__('messages.quiz.quiz_title'))
                                            ->validationAttribute(__('messages.quiz.title'))
                                            ->required(),
                                        Select::make('category_id')
                                            ->label(__('messages.quiz.select_category') . ':')
                                            ->placeholder(__('messages.quiz.select_category'))
                                            ->validationAttribute(__('messages.quiz.category'))
                                            ->options(function () {
                                                return Category::all()->pluck('name', 'id');
                                            })
                                            ->searchable()
                                            ->required()
                                            ->preload()
                                            ->native(false)
                                    ]),
                                    Section::make()
                                        ->schema([
                                            Select::make('quiz_type')
                                                ->label(__('messages.quiz.question_type') . ':')
                                                ->options(function(){
                                                    $options = Quiz::getQuizTypeOptions();
                                                    $sub = getActiveSubscription();
                                                    $allowed = $sub && $sub->plan ? (array)$sub->plan->allowed_question_types : [];
                                                    if (empty($allowed)) {
                                                        return $options;
                                                    }
                                                    $map = [
                                                        0 => 'multiple_choice',
                                                        1 => 'single_choice',
                                                        2 => 'true_false',
                                                        3 => 'open_ended',
                                                    ];
                                                    return collect($options)->filter(function($label, $key) use ($allowed, $map){
                                                        return isset($map[$key]) ? in_array($map[$key], $allowed) : true;
                                                    });
                                                })
                                                ->default(0)
                                                ->searchable()
                                                ->required()
                                                ->preload()
                                                ->live()
                                                ->native(false)
                                                ->placeholder(__('messages.quiz.select_question'))
                                                ->validationAttribute(__('messages.quiz.question_type')),
                                            Select::make('diff_level')
                                                ->label(__('messages.quiz.difficulty') . ':')
                                                ->options(Quiz::getDiffLevelOptions())
                                                ->default(0)
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->placeholder(__('messages.quiz.select_difficulty'))
                                                ->validationAttribute(__('messages.quiz.difficulty')),
                                            TextInput::make('max_questions')
                                                ->numeric()
                                                ->integer()
                                                ->required()
                                                ->minValue(1)
                                                ->default(25)
                                                ->label(__('messages.quiz.num_of_questions') . ':')
                                                ->hintIcon('heroicon-o-information-circle')
                                                ->hintIconTooltip(function(){
                                                    $sub = getActiveSubscription();
                                                    $limit = $sub && $sub->plan ? $sub->plan->max_questions_per_exam : null;
                                                    return $limit !== null && (int)$limit >= 0
                                                        ? __('Maximum :n number of questions allowed.', ['n' => $limit])
                                                        : __('messages.quiz.max_no_of_quiz');
                                                })
                                                ->placeholder(__('messages.quiz.number_of_questions'))
                                                ->validationAttribute(__('messages.quiz.num_of_questions'))
                                                ->live()
                                                ->rule(function(){
                                                    $sub = getActiveSubscription();
                                                    $limit = $sub && $sub->plan ? $sub->plan->max_questions_per_exam : null;
                                                    return ($limit !== null && (int)$limit >= 0) ? 'max:'.$limit : null;
                                                })
                                                ->maxValue(function(){
                                                    $sub = getActiveSubscription();
                                                    $limit = $sub && $sub->plan ? $sub->plan->max_questions_per_exam : null;
                                                    return ($limit !== null && (int)$limit >= 0) ? $limit : 25;
                                                }),
                                            Select::make('language')
                                                ->label(__('messages.home.language') . ':')
                                                ->options(getAllLanguages())
                                                ->preload()
                                                ->searchable()
                                                ->native(false)
                                                ->default('en')
                                                ->validationAttribute(__('messages.home.language'))
                                        ])
                                        ->columns(2),
                                ])
                                ->columnSpan(1),

                            Grid::make(1)
                                ->schema([
                                    Tabs::make('Tabs')
                                        ->tabs([
                                            Tab::make('Prompt')
                                                ->label('Prompt')
                                                ->schema([
                                                    Textarea::make('custom_prompt')
                                                        ->label('Prompt:')
                                                        ->placeholder('Describe exactly what the exam should contain (topics, tone, constraints)')
                                                        ->helperText('Optional: AI will prioritize this instruction if provided.')
                                                        ->rows(4)
                                                        ->cols(10),
                                                ])->id('prompt-tab'),
                                            Tab::make('Text')
                                                ->label(__('messages.quiz.text'))
                                                ->schema([
                                                    Textarea::make('quiz_description_text')
                                                        ->label(__('messages.quiz.description') . ':')
                                                        ->hintIcon('heroicon-o-document-text')
                                                        ->hintIconTooltip(__('messages.quiz.text_tab_help'))
                                                        ->hintColor('primary')
                                                        ->placeholder(__('messages.quiz.quiz_description'))
                                                        ->formatStateUsing(function ($get, $operation) {
                                                            if ($operation == 'edit' && $get('type') == 1) {
                                                                return $get('quiz_description');
                                                            }
                                                        })
                                                        ->required(function ($get) {
                                                            return getTabType() == 1 || $get('type') == 1;
                                                        })
                                                        ->live()
                                                        ->validationAttribute(__('messages.quiz.description'))
                                                        ->rows(5)
                                                        ->cols(10),
                                                ]),
                                            Tab::make('Subject')
                                                ->label(__('messages.quiz.subject'))
                                                ->schema([
                                                    TextInput::make('quiz_description_sub')
                                                        ->label(__('messages.quiz.subject') . ':')
                                                        ->hintIcon('heroicon-o-light-bulb')
                                                        ->hintIconTooltip(__('messages.quiz.subject_tab_help'))
                                                        ->hintColor('primary')
                                                        ->placeholder(__('messages.quiz.e_g_biology'))
                                                        ->formatStateUsing(function ($get, $operation) {
                                                            if ($operation == 'edit' && $get('type') == 2) {
                                                                return $get('quiz_description');
                                                            }
                                                        })
                                                        ->required(function ($get) {
                                                            return getTabType() == 2 || $get('type') == 2;
                                                        })
                                                        ->live()
                                                        ->validationAttribute(__('messages.quiz.subject'))
                                                        ->maxLength(250)
                                                        ->helperText(__('messages.quiz.enter_a_subject_to_generate_question_about'))
                                                        ->autocomplete('off'),
                                                ]),
                                            Tab::make('URL')
                                                ->label(__('messages.quiz.url'))
                                                ->schema([
                                                    TextInput::make('quiz_description_url')
                                                        ->label(__('messages.quiz.url') . ':')
                                                        ->hintIcon('heroicon-o-link')
                                                        ->hintIconTooltip(__('messages.quiz.url_tab_help'))
                                                        ->hintColor('primary')
                                                        ->formatStateUsing(function ($get, $operation) {
                                                            if ($operation == 'edit' && $get('type') == 3) {
                                                                return $get('quiz_description');
                                                            }
                                                        })
                                                        ->required(function ($get) {
                                                            return getTabType() == 3 || $get('type') == 3;
                                                        })
                                                        ->live()
                                                        ->validationAttribute(__('messages.quiz.url'))
                                                        ->url()
                                                        ->placeholder(__('messages.quiz.please_enter_url')),
                                                ]),
                                            Tab::make('Upload')
                                                ->label(__('messages.quiz.upload'))
                                                ->schema([
                                                    SpatieMediaLibraryFileUpload::make('file_upload')
                                                        ->label(__('messages.quiz.document') . ':')
                                                        ->hintIcon('heroicon-o-document-arrow-up')
                                                        ->hintIconTooltip(__('messages.quiz.file_upload_hint'))
                                                        ->validationAttribute(__('messages.quiz.document'))
                                                        ->disk(config('app.media_disk'))
                                                        ->required(function ($get) {
                                                            return getTabType() == 4 || $get('type') == 4;
                                                        })
                                                        ->live()
                                                        ->collection(Quiz::QUIZ_PATH)
                                                        ->acceptedFileTypes(['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                                        ->maxSize(function () {
                                                            $sub = getActiveSubscription();
                                                            $mb = $sub && optional($sub->plan)->max_pdf_upload_mb ? (int) $sub->plan->max_pdf_upload_mb : 10; // default 10MB if not set
                                                            return max(1, $mb) * 1024; // convert MB to KB as Filament expects KB
                                                        })
                                                        ->helperText(function () {
                                                            $sub = getActiveSubscription();
                                                            $mb = $sub && optional($sub->plan)->max_pdf_upload_mb ? (int) $sub->plan->max_pdf_upload_mb : 10;
                                                            return 'Maximum file size: ' . $mb . 'MB. PDF page limits may apply based on your plan.';
                                                        }),
                                                ]),
                                        ])
                                        ->activeTab(function ($get) {
                                            return $get('type') ?? 1; // restore original behavior; Prompt not default-selected
                                        })
                                        ->extraAttributes([
                                            'wire:click' => 'currentActiveTab',
                                        ])
                                        ->persistTabInQueryString(),
                                    Section::make()
                                        ->schema([
                                            Toggle::make("time_configuration")
                                                ->live()
                                                ->label(__('messages.quiz.time_configuration') . ':'),
                                            DatePicker::make('quiz_expiry_date')
                                                ->placeholder(__('messages.quiz.expiry_date'))
                                                ->minDate(now()->format('Y-m-d'))
                                                ->label(__('messages.quiz.expiry_date') . ':')
                                                ->native(false)
                                                ->hintAction(
                                                    Action::make('clearDate')
                                                        ->iconButton()
                                                        ->icon('heroicon-o-x-circle')
                                                        ->tooltip(__('messages.common.clear_date'))
                                                        ->action(function (\Filament\Forms\Set $set) {
                                                            $set('quiz_expiry_date', null);
                                                        })
                                                ),
                                            Section::make()
                                                ->schema([
                                                    TextInput::make('time')
                                                        ->numeric()
                                                        ->placeholder(__('messages.quiz.time'))
                                                        ->required()
                                                        ->minValue(1)
                                                        ->rules(['integer', 'min:1'])
                                                        ->label(__('messages.quiz.time_label') . ':')
                                                        ->extraAttributes([
                                                            'onkeydown' => "if(event.key === '-' || event.key === '+' || event.key === 'e'){ event.preventDefault(); }"
                                                        ]),

                                                    Radio::make('time_type')
                                                        ->options([
                                                            1 => __('messages.quiz.time_question'),
                                                            2 => __('messages.quiz.time_quiz'),
                                                        ])
                                                        ->required()
                                                        ->label(__('messages.quiz.time_type') . ':'),

                                                ])->live()->columns(2)->hidden(function ($get) {
                                                    return !$get('time_configuration');
                                                }),
                                        ])
                                        ->columns(2),
                                    Section::make()
                                        ->schema([
                                            Toggle::make('is_public')
                                                ->label(__('messages.quiz.make_public') . ':')
                                                ->default(false)
                                                ->helperText(__('messages.quiz.make_public_help')),
                                        ])
                                        ->columns(1),
                                ])
                                ->columnSpan(1),
                        ])
                        ->columns(2),
                ]),

            Repeater::make('questions')
                ->label(__('messages.common.questions'))
                ->columnSpanFull()
                ->reorderableWithDragAndDrop(true)
                ->addable(false)
                ->schema([
                    TextInput::make('title')
                        ->label(__('messages.common.question') . ':')
                        ->validationAttribute(__('messages.common.question'))
                        ->required(),
                    CheckboxList::make('is_correct')
                        ->options(fn($get) => collect($get('answers'))->mapWithKeys(fn($answer, $index) => [$index => $answer['title']])->toArray())
                        ->required()
                        ->minItems(1)
                        ->maxItems(function (Get $get) {
                            $quizType = $get('../../quiz_type');
                            return $quizType == Quiz::SINGLE_CHOICE ? 1 : 4;
                        })
                        ->columns(2)
                        ->validationAttribute(__('messages.common.answer'))
                        ->label(__('messages.common.answer') . ':')
                        ->afterStateUpdated(function ($state, $set, $get) {
                            $answers = $get('answers') ?? [];

                            foreach ($answers as $index => $answer) {
                                $answers[$index]['is_correct'] = in_array($index, $state);
                            }

                            $set('answers', $answers);
                        })
                        ->afterStateHydrated(function ($set, $get, $state) {
                            $correctAnswer = $get('is_correct');
                            if (is_array($correctAnswer)) {
                                $set('is_correct', $correctAnswer);
                            } elseif ($correctAnswer !== null) {
                                $set('is_correct', [$correctAnswer]);
                            }
                        })
                        ->visible(fn(Get $get) => !empty($get('answers'))),
                ])
                // Always show questions on edit page and ensure visibility after creation
                ->visible(function(string $operation): bool {
                    if ($operation === 'edit') {
                        return true; // Always show on edit page
                    }
                    return false;
                })
                ->addable(false)
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),

            Repeater::make('custom_questions')
                ->columnSpanFull()
                ->label('')
                ->reorderableWithDragAndDrop(true)
                ->addable(false)
                ->hidden(fn(string $operation): bool => $operation === 'create')
                ->schema([
                    TextInput::make('title')
                        ->label(__('messages.common.question') . ':')
                        ->placeholder(__('messages.common.question'))
                        ->validationAttribute(__('messages.common.answer'))
                        ->required(),
                    Repeater::make('answers')
                        ->label(__('messages.common.answer') . ':')
                        ->addActionLabel(__('messages.common.add_answer'))
                        ->defaultItems(function (Get $get) {
                            $quizType = $get('../../quiz_type');
                            return $quizType == Quiz::OPEN_ENDED ? 0 : 2;
                        })
                        ->minItems(function (Get $get) {
                            $quizType = $get('../../quiz_type');
                            return $quizType == Quiz::OPEN_ENDED ? 0 : 2;
                        })
                        ->maxItems(function (Get $get) {
                            $quizType = $get('../../quiz_type');
                            return $quizType == Quiz::OPEN_ENDED ? 0 : 4;
                        })
                        ->validationAttribute(__('messages.common.answer'))
                        ->grid(2)
                        ->schema([
                            Group::make([
                                TextInput::make('title')
                                    ->placeholder(__('messages.common.answer'))
                                    ->label(__('messages.common.answer') . ':')
                                    ->required()
                                    ->columnSpan(3),
                                Toggle::make('is_correct')
                                    ->inline(false)
                                    ->label(__('messages.common.is_correct') . ':'),
                            ])->columns(4),
                        ])
                        ->required(function (Get $get) {
                            $quizType = $get('../../quiz_type');
                            return $quizType != Quiz::OPEN_ENDED;
                        }),
                ]),

            Section::make()
                ->schema([
                    Placeholder::make('created_questions_counter')
                        ->label('Created Questions')
                        ->content(function (?\Illuminate\Database\Eloquent\Model $record) {
                            if (! $record) return '—';
                            return (string) $record->questions()->count();
                        })
                        ->hint('Updates after AI adds more questions')
                        ->columnSpanFull(),
                    // Hide preview to avoid duplicate/confusing UI; editable list is source of truth
                ])
                ->visible(false)
                ->columnSpanFull(),

        ];
    }
}
