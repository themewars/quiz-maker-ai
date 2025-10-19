<?php

namespace App\Filament\User\Pages;

use App\Enums\UserSidebar;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

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
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label(__('messages.ai_notes.generate_notes'))
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->action('generateNotes'),
        ];
    }

    public function generateNotes()
    {
        $this->form->validate();
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
                $sampleNotes .= "• **Historical Context**: Understanding the background and causes\n";
                $sampleNotes .= "• **Major Events**: Key incidents and developments\n";
                $sampleNotes .= "• **Impact**: Long-term effects and consequences\n";
                $sampleNotes .= "• **Significance**: Why this topic matters today\n\n";
                $sampleNotes .= "## Detailed Analysis\n";
                $sampleNotes .= "### Background\n";
                $sampleNotes .= "{$topic} emerged from complex historical circumstances that shaped its development and impact.\n\n";
                $sampleNotes .= "### Main Characteristics\n";
                $sampleNotes .= "The key features of {$topic} include:\n";
                $sampleNotes .= "- Primary characteristics and defining elements\n";
                $sampleNotes .= "- Important relationships and connections\n";
                $sampleNotes .= "- Unique aspects that distinguish it from similar concepts\n\n";
                $sampleNotes .= "### Examples and Applications\n";
                $sampleNotes .= "Real-world examples demonstrate how {$topic} functions:\n";
                $sampleNotes .= "- Practical applications in {$subject}\n";
                $sampleNotes .= "- Case studies and notable instances\n";
                $sampleNotes .= "- Contemporary relevance and modern applications\n\n";
                $sampleNotes .= "## Summary\n";
                $sampleNotes .= "In conclusion, {$topic} represents a fundamental concept in {$subject} that continues to influence our understanding and practice. Its historical significance, practical applications, and ongoing relevance make it essential for comprehensive study and application in various contexts.\n\n";
                $sampleNotes .= "### Key Takeaways\n";
                $sampleNotes .= "• Understanding the core principles and mechanisms\n";
                $sampleNotes .= "• Recognizing patterns and relationships\n";
                $sampleNotes .= "• Applying knowledge in practical situations\n";
                $sampleNotes .= "• Connecting to broader concepts and themes\n";
                break;
                
            case 'bullet_points':
                $sampleNotes .= "## {$topic} - Comprehensive Key Points\n\n";
                $sampleNotes .= "### Core Concepts\n";
                $sampleNotes .= "• **Definition**: {$topic} refers to a complex phenomenon in {$subject} that encompasses multiple dimensions and aspects\n";
                $sampleNotes .= "• **Historical Background**: The origins and development of {$topic} can be traced through significant historical periods\n";
                $sampleNotes .= "• **Key Characteristics**: Essential features that define and distinguish {$topic} from related concepts\n";
                $sampleNotes .= "• **Scope and Scale**: The extent and magnitude of {$topic}'s influence and impact\n\n";
                $sampleNotes .= "### Important Elements\n";
                $sampleNotes .= "• **Primary Components**: The main parts that constitute {$topic}\n";
                $sampleNotes .= "• **Supporting Factors**: Elements that contribute to {$topic}'s development and maintenance\n";
                $sampleNotes .= "• **Interconnected Systems**: How {$topic} relates to other concepts and systems\n";
                $sampleNotes .= "• **Dynamic Processes**: The ongoing changes and evolution within {$topic}\n\n";
                $sampleNotes .= "### Practical Applications\n";
                $sampleNotes .= "• **Real-World Examples**: Concrete instances where {$topic} is evident\n";
                $sampleNotes .= "• **Contemporary Relevance**: How {$topic} applies to current situations and challenges\n";
                $sampleNotes .= "• **Cross-Disciplinary Impact**: Influence across different fields and domains\n";
                $sampleNotes .= "• **Future Implications**: Potential developments and trends related to {$topic}\n\n";
                $sampleNotes .= "### Analysis and Interpretation\n";
                $sampleNotes .= "• **Critical Perspectives**: Different viewpoints and interpretations of {$topic}\n";
                $sampleNotes .= "• **Comparative Analysis**: How {$topic} compares to similar concepts\n";
                $sampleNotes .= "• **Significance Assessment**: The importance and value of understanding {$topic}\n";
                $sampleNotes .= "• **Learning Applications**: How knowledge of {$topic} can be applied in practice\n";
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
                $sampleNotes .= "## {$topic} - Comprehensive Analysis\n\n";
                $sampleNotes .= "### Introduction\n";
                $sampleNotes .= "{$topic} represents a significant and multifaceted concept within the field of {$subject}. This comprehensive analysis explores the various dimensions, implications, and applications of {$topic}, providing a thorough understanding of its importance and relevance.\n\n";
                $sampleNotes .= "### Historical Context and Development\n";
                $sampleNotes .= "The evolution of {$topic} can be understood through its historical development and the various factors that have shaped its current form. Understanding this context is crucial for appreciating the complexity and significance of {$topic} in contemporary {$subject}.\n\n";
                $sampleNotes .= "### Core Principles and Mechanisms\n";
                $sampleNotes .= "At its foundation, {$topic} operates through several key principles and mechanisms that define its nature and function. These include:\n";
                $sampleNotes .= "- Fundamental processes and operations\n";
                $sampleNotes .= "- Underlying structures and frameworks\n";
                $sampleNotes .= "- Essential relationships and interactions\n";
                $sampleNotes .= "- Core methodologies and approaches\n\n";
                $sampleNotes .= "### Detailed Analysis and Components\n";
                $sampleNotes .= "A comprehensive examination of {$topic} reveals multiple interconnected components that work together to create its overall impact and effectiveness. Each component contributes unique aspects to the understanding and application of {$topic}.\n\n";
                $sampleNotes .= "### Practical Applications and Examples\n";
                $sampleNotes .= "The real-world applications of {$topic} demonstrate its practical value and relevance across various contexts. These applications show how theoretical understanding translates into practical benefits and solutions.\n\n";
                $sampleNotes .= "### Contemporary Relevance and Future Implications\n";
                $sampleNotes .= "In today's rapidly changing world, {$topic} continues to evolve and adapt to new challenges and opportunities. Understanding its current relevance and potential future developments is essential for staying informed and prepared.\n\n";
                $sampleNotes .= "### Conclusion\n";
                $sampleNotes .= "The study of {$topic} provides valuable insights into complex phenomena within {$subject}. Its comprehensive understanding enables better decision-making, problem-solving, and innovation across various domains. The continued exploration and application of {$topic} will remain crucial for advancing knowledge and addressing contemporary challenges.\n\n";
                $sampleNotes .= "### Key Learning Points\n";
                $sampleNotes .= "- Comprehensive understanding of core concepts and principles\n";
                $sampleNotes .= "- Recognition of practical applications and real-world relevance\n";
                $sampleNotes .= "- Awareness of historical context and contemporary significance\n";
                $sampleNotes .= "- Ability to apply knowledge in various contexts and situations\n";
                break;
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
