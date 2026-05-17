<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Services\LogFileService;
use Illuminate\Support\Facades\Hash;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class ViewLogs extends Page
{
    use HasPageShield;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.view-logs';

    public ?string $selectedFile = null;

    public ?int $tailLines = 100;

    public ?bool $autoRefresh = false;

    public ?string $logContent = null;

    public ?string $fileSize = null;

    public ?string $lastModified = null;

    public ?string $confirmationPassword = null;

    public function mount(): void
    {
        $this->tailLines = 100;
        $this->autoRefresh = false;
        $this->form->fill();
        $this->loadLogContent();
    }

    public function updatedSelectedFile(): void
    {
        $this->loadLogContent();
    }

    public function updatedTailLines(): void
    {
        $this->loadLogContent();
    }

    public function loadLogContent(): void
    {
        $logFileService = app(LogFileService::class);
        
        if (empty($this->selectedFile)) {
            $this->logContent = "No file selected.";
            $this->fileSize = null;
            $this->lastModified = null;
            return;
        }

        $fileInfo = $logFileService->getFileInfo($this->selectedFile);
        
        if (! $fileInfo) {
            $this->logContent = "Invalid file selection or file not found.";
            $this->fileSize = null;
            $this->lastModified = null;
            return;
        }

        $this->fileSize = $fileInfo['size'];
        $this->lastModified = $fileInfo['last_modified'];
        $this->logContent = $logFileService->readFile($this->selectedFile, $this->tailLines);
    }

    public function refresh(): void
    {
        $this->loadLogContent();
    }

    public function clearLog(): void
    {
        $logFileService = app(LogFileService::class);
        
        if ($logFileService->clearFile($this->selectedFile)) {
            $this->loadLogContent();
            Notification::make()
                ->title('Log file cleared successfully.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Invalid file selection.')
                ->danger()
                ->send();
        }
    }

    public function clearLogWithPassword(?string $password): void
    {
        // Verify password
        if (empty($password)) {
            Notification::make()
                ->title('Password is required to clear log file.')
                ->danger()
                ->send();

            return;
        }

        $user = \Illuminate\Support\Facades\Auth::user();
        if (! $user || ! Hash::check($password, $user->getAuthPassword())) {

            Notification::make()
                ->title('Invalid password. Log file was not cleared.')
                ->danger()
                ->send();

            return;
        }

        $logFileService = app(LogFileService::class);
        
        if ($logFileService->clearFile($this->selectedFile)) {
            $this->loadLogContent();
            Notification::make()
                ->title('Log file cleared successfully.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Invalid file selection.')
                ->danger()
                ->send();
        }
    }

    public function download(): void
    {
        $logFileService = app(LogFileService::class);
        $url = $logFileService->getDownloadUrl($this->selectedFile);
        
        if ($url) {
            $this->dispatch('download-log', [
                'url' => $url,
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('')
                    ->columns(2)
                    ->schema([
                        Select::make('selectedFile')
                            ->label('Log File')
                            ->options(app(LogFileService::class)->getLogFiles())
                            ->placeholder('Select a log file')
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn() => $this->loadLogContent()),

                        TextInput::make('tailLines')
                            ->label('Show Last N Lines')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10000)
                            ->default(100)
                            ->helperText('0 = show entire file (use with caution on large files)')
                            ->live()
                            ->afterStateUpdated(fn() => $this->loadLogContent()),

                        Toggle::make('autoRefresh')
                            ->label('Auto Refresh (30s)')
                            ->helperText('Automatically refresh content every 30 seconds'),
                    ]),
            ]);
    }



    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action(fn() => $this->refresh()),

            Action::make('download')
                ->label('Download')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn() => $this->download())
                ->visible(fn() => ! empty($this->selectedFile)),

            Action::make('clear')
                ->label('Clear Log')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Clear Log File')
                ->modalDescription('This action cannot be undone. Please enter your password to confirm.')
                ->form([
                    TextInput::make('confirmationPassword')
                        ->label('Your Password')
                        ->password()
                        ->required()
                        ->autocomplete('current-password')
                        ->placeholder('Enter your password to confirm'),
                ])
                ->action(fn(array $data) => $this->clearLogWithPassword($data['confirmationPassword'] ?? null))
                ->visible(fn() => ! empty($this->selectedFile)),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'View Logs';
    }

    public function getTitle(): string
    {
        return 'Log Viewer';
    }
}
