<?php

namespace Codedor\FilamentMailTemplates\Filament\Resources;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Resources\Concerns\Translatable;
use Codedor\FilamentMailTemplates\Filament\Resources\MailTemplateResource\Pages;
use Codedor\FilamentMailTemplates\Models\MailTemplate;
use Codedor\FilamentPlaceholderInput\Filament\Forms\Components\PlaceholderInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;

class MailTemplateResource extends Resource
{
	use Translatable;

	protected static ?string $model = MailTemplate::class;

    public static function getNavigationGroup(): ?string
    {
        return config(
            'filament-mail-templates.navigation.templates.group',
            parent::getNavigationGroup()
        );
    }

    public static function getNavigationIcon(): string
    {
        return config(
            'filament-mail-templates.navigation.templates.icon',
            parent::getNavigationIcon()
        );
    }

    public static function shouldRegisterNavigation(): bool
    {
        return config(
            'filament-mail-templates.navigation.templates.shown',
            parent::shouldRegisterNavigation()
        );
    }

    public static function form(Form $form): Form
    {

        return $form->schema([
            Grid::make()
                ->columns(['lg' => 3])
                ->schema([
					Section::make('Info')
						->columnSpan(2)
						->schema([
							Placeholder::make('identifier')
								->content(fn (MailTemplate $record) => $record->identifier),

							Placeholder::make('description')
								->content(fn (MailTemplate $record) => $record->description),

							Repeater::make('to_email')
								->helperText('If left empty, the sites default e-mail will be used.')
								->label('Target e-mails')
								->hidden(fn (MailTemplate $record) => ! $record->getMailTemplate()->hasTargetField())
								->schema([
									Grid::make()->schema([
										TextInput::make('email')
											->required(),

										Select::make('type')
											->required()
											->options([
												'to' => 'Normal',
												'cc' => 'CC',
												'bcc' => 'BCC',
											]),
									]),
								]),
						]),
					Section::make('template')
						->heading('Template')
						->columns(3)->schema([
						Grid::make(1)
							->columnSpan(['lg' => 2])
							->schema([
								TextInput::make('subject'),
								//TiptapEditor::make('body'),
								RichEditor::make('body')->withAI(function (){
									return "Kan je een voorbeeld mail schrijven voor een algemene e-mail en moeten aansluiten bij de belastingdienstgerelateerde diensten die het portaal aanbiedt. Genereer de template als markdown zonder onderwerp.  en geef alleen de tekst in de markdown terug";
								}),
							]),

						PlaceholderInput::make('variables')
							->name('variables')
							->linksWith(["subject", "body"])
							->copyable(),
					]),
                ])

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('identifier'),

                TextColumn::make('description'),

                //LocalesColumn::make('online'),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->url(fn (MailTemplate $record) => self::getUrl('preview', [$record]))
                    ->label(__('filament-mail-templates::preview.button label'))
                    ->icon('heroicon-o-eye'),

                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMailTemplates::route('/'),
            'preview' => Pages\PreviewMailTemplate::route('/{record}/preview'),
            'edit' => Pages\EditMailTemplate::route('/{record}/edit'),
        ];
    }
}
