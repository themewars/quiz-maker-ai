<?php

namespace App\Filament\User\Pages;

use App\Enums\UserSidebar;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class AINotesGenerator extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.user.pages.ai-notes-generator';

    protected static ?int $navigationSort = UserSidebar::AI_NOTES_GENERATOR->value;

    protected static ?string $navigationGroup = 'Free Tools';

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('messages.ai_notes.title');
    }

    public function getTitle(): string
    {
        return __('messages.ai_notes.title');
    }

    public function mount()
    {
        $this->form->fill([
            'topic' => '',
            'subject' => '',
            'notes_type' => 'summary',
            'length' => 'medium',
            'language' => 'en',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('messages.ai_notes.title'))
                    ->description(__('messages.ai_notes.description'))
                    ->schema([
                        TextInput::make('topic')
                            ->label(__('messages.ai_notes.topic'))
                            ->placeholder('e.g., Photosynthesis, World War II, Machine Learning')
                            ->required()
                            ->maxLength(255),

                        Select::make('subject')
                            ->label(__('messages.ai_notes.subject_category'))
                            ->options([
                                'science' => __('messages.ai_notes.subjects.science'),
                                'history' => __('messages.ai_notes.subjects.history'),
                                'mathematics' => __('messages.ai_notes.subjects.mathematics'),
                                'literature' => __('messages.ai_notes.subjects.literature'),
                                'geography' => __('messages.ai_notes.subjects.geography'),
                                'computer_science' => __('messages.ai_notes.subjects.computer_science'),
                                'biology' => __('messages.ai_notes.subjects.biology'),
                                'chemistry' => __('messages.ai_notes.subjects.chemistry'),
                                'physics' => __('messages.ai_notes.subjects.physics'),
                                'economics' => __('messages.ai_notes.subjects.economics'),
                                'other' => __('messages.ai_notes.subjects.other'),
                            ])
                            ->required(),

                        Select::make('notes_type')
                            ->label(__('messages.ai_notes.notes_type'))
                            ->options([
                                'summary' => __('messages.ai_notes.notes_types.summary'),
                                'detailed' => __('messages.ai_notes.notes_types.detailed'),
                                'outline' => __('messages.ai_notes.notes_types.outline'),
                                'bullet_points' => __('messages.ai_notes.notes_types.bullet_points'),
                                'mind_map' => __('messages.ai_notes.notes_types.mind_map'),
                            ])
                            ->required(),

                        Select::make('length')
                            ->label(__('messages.ai_notes.length'))
                            ->options([
                                'short' => __('messages.ai_notes.lengths.short'),
                                'medium' => __('messages.ai_notes.lengths.medium'),
                                'long' => __('messages.ai_notes.lengths.long'),
                            ])
                            ->required(),

                        Select::make('language')
                            ->label(__('messages.ai_notes.language'))
                            ->options([
                                'en' => 'English',
                                'hi' => 'Hindi',
                                'es' => 'Spanish',
                                'fr' => 'French',
                                'de' => 'German',
                                'it' => 'Italian',
                                'pt' => 'Portuguese',
                                'ru' => 'Russian',
                                'zh' => 'Chinese',
                                'ar' => 'Arabic',
                            ])
                            ->required(),
                    ])
                    ->columns(2)
                    ->actions([
                        Action::make('generate')
                            ->label(__('messages.ai_notes.generate_notes'))
                            ->icon('heroicon-o-sparkles')
                            ->color('primary')
                            ->action('generateNotes'),
                    ]),
            ])
            ->statePath('data');
    }

    public function generateNotes()
    {
        $data = $this->form->getState();
        
        try {
            // Simulate AI API call (replace with actual AI service)
            $prompt = $this->buildPrompt($data);
            
            // For now, we'll create a sample response
            // In production, you would call OpenAI, Gemini, or another AI service
            $notes = $this->generateSampleNotes($data);
            
            Notification::make()
                ->title(__('messages.ai_notes.notes_generated_successfully'))
                ->success()
                ->send();
                
            // Store the generated notes in session or database
            session(['generated_notes' => $notes]);
            
            // Redirect to view the generated notes
            return redirect()->route('filament.user.pages.ai-notes-generator');
            
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('messages.ai_notes.error_generating_notes'))
                ->body(__('messages.ai_notes.try_again_later'))
                ->danger()
                ->send();
        }
    }

    private function buildPrompt(array $data): string
    {
        $lengthMap = [
            'short' => 'brief and concise',
            'medium' => 'moderate length',
            'long' => 'comprehensive and detailed'
        ];

        $typeMap = [
            'summary' => 'summary format',
            'detailed' => 'detailed explanation',
            'outline' => 'outline structure',
            'bullet_points' => 'bullet point format',
            'mind_map' => 'mind map structure'
        ];

        return "Generate {$lengthMap[$data['length']]} notes about '{$data['topic']}' in {$data['subject']} subject. 
                Format: {$typeMap[$data['notes_type']]}. 
                Language: {$data['language']}. 
                Include key concepts, definitions, examples, and important points.";
    }

    private function generateSampleNotes(array $data): string
    {
        $topic = $data['topic'];
        $subject = $data['subject'];
        $type = $data['notes_type'];
        
        $sampleNotes = "# Notes on {$topic}\n\n";
        
        switch ($type) {
            case 'summary':
                $sampleNotes .= "## Overview\n";
                $sampleNotes .= "This is a comprehensive summary of {$topic} in the field of {$subject}.\n\n";
                $sampleNotes .= "## Key Points\n";
                $sampleNotes .= "• Important concept 1\n";
                $sampleNotes .= "• Important concept 2\n";
                $sampleNotes .= "• Important concept 3\n\n";
                $sampleNotes .= "## Summary\n";
                $sampleNotes .= "In conclusion, {$topic} is a fundamental concept in {$subject} that...\n";
                break;
                
            case 'bullet_points':
                $sampleNotes .= "## {$topic} - Key Points\n\n";
                $sampleNotes .= "• **Definition**: Basic definition of {$topic}\n";
                $sampleNotes .= "• **Characteristics**: Main features and properties\n";
                $sampleNotes .= "• **Examples**: Real-world applications\n";
                $sampleNotes .= "• **Importance**: Why it matters in {$subject}\n";
                $sampleNotes .= "• **Related Concepts**: Connected topics\n";
                break;
                
            case 'outline':
                $sampleNotes .= "# {$topic} - Outline\n\n";
                $sampleNotes .= "I. Introduction\n";
                $sampleNotes .= "   A. Definition\n";
                $sampleNotes .= "   B. Background\n";
                $sampleNotes .= "II. Main Concepts\n";
                $sampleNotes .= "   A. Concept 1\n";
                $sampleNotes .= "   B. Concept 2\n";
                $sampleNotes .= "III. Applications\n";
                $sampleNotes .= "IV. Conclusion\n";
                break;
                
            default:
                $sampleNotes .= "## {$topic}\n\n";
                $sampleNotes .= "This is a detailed explanation of {$topic} in {$subject}.\n\n";
                $sampleNotes .= "### Introduction\n";
                $sampleNotes .= "{$topic} is an important concept that...\n\n";
                $sampleNotes .= "### Main Content\n";
                $sampleNotes .= "The key aspects include...\n\n";
                $sampleNotes .= "### Conclusion\n";
                $sampleNotes .= "Understanding {$topic} is essential for...\n";
        }
        
        return $sampleNotes;
    }

    public function getGeneratedNotes(): ?string
    {
        return session('generated_notes');
    }

    public function clearNotes()
    {
        session()->forget('generated_notes');
        Notification::make()
            ->title(__('messages.ai_notes.notes_cleared'))
            ->success()
            ->send();
    }
}
