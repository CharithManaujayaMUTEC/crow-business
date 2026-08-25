<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationLabel = 'Employees';

    protected static ?string $pluralModelLabel = 'Employees';

    protected static ?string $modelLabel = 'Employee';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basic Information')
                ->schema([
                    TextInput::make('employee_no')
                        ->label('Employee Number')
                        ->required()
                        ->unique(ignoreRecord: true),

                    TextInput::make('name')
                        ->label('Full Name')
                        ->required(),

                    TextInput::make('preferred_name')
                        ->label('Preferred Name'),

                    FileUpload::make('profile_photo')
                        ->label('Profile Photo')
                        ->image()
                        ->disk('public')
                        ->directory('employees/photos')
                        ->imageEditor(),

                    DatePicker::make('date_of_birth')
                        ->label('Date of Birth'),

                    Select::make('gender')
                        ->options([
                            'male' => 'Male',
                            'female' => 'Female',
                            'other' => 'Other',
                            'prefer_not_to_say' => 'Prefer not to say',
                        ]),

                    TextInput::make('nic_passport')
                        ->label('NIC / Passport Number'),

                    TextInput::make('phone')
                        ->label('Personal Phone'),

                    TextInput::make('personal_email')
                        ->label('Personal Email')
                        ->email(),

                    TextInput::make('email')
                        ->label('Work Email')
                        ->email(),

                    Textarea::make('address')
                        ->label('Residential Address')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Employment Information')
                ->schema([
                    Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'inactive' => 'Inactive',
                            'resigned' => 'Resigned',
                            'terminated' => 'Terminated',
                        ])
                        ->default('active')
                        ->required(),

                    Select::make('employment_type')
                        ->options([
                            'permanent' => 'Permanent',
                            'probation' => 'Probation',
                            'contract' => 'Contract',
                            'internship' => 'Internship',
                            'part_time' => 'Part-time',
                        ]),

                    TextInput::make('department'),

                    TextInput::make('designation')
                        ->label('Designation / Position'),

                    Select::make('reporting_manager_id')
                        ->label('Reporting Manager')
                        ->relationship(
                            name: 'reportingManager',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query, $record) =>
                                $record
                                    ? $query->whereKeyNot($record->id)
                                    : $query
                        )
                        ->searchable()
                        ->preload(),

                    TextInput::make('work_location'),

                    Select::make('work_mode')
                        ->options([
                            'on_site' => 'On-site',
                            'remote' => 'Remote',
                            'hybrid' => 'Hybrid',
                        ]),

                    DatePicker::make('join_date')
                        ->label('Joining Date'),

                    DatePicker::make('probation_start_date'),

                    DatePicker::make('probation_end_date'),

                    DatePicker::make('confirmation_date'),

                    DatePicker::make('contract_start_date'),

                    DatePicker::make('contract_end_date'),

                    DatePicker::make('resignation_date'),

                    DatePicker::make('termination_date'),

                    Textarea::make('reason_for_leaving')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Job Role')
                ->schema([
                    TextInput::make('job_title')
                        ->label('Job Title'),

                    TextInput::make('access_level')
                        ->label('Access Level / Role'),

                    Textarea::make('job_description')
                        ->columnSpanFull(),

                    Repeater::make('responsibilities')
                        ->relationship()
                        ->schema([
                            TextInput::make('title')
                                ->label('Responsibility')
                                ->required(),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Add Responsibility')
                        ->reorderable()
                        ->columnSpanFull(),

                    Repeater::make('skills')
                        ->relationship()
                        ->schema([
                            TextInput::make('skill')
                                ->required(),

                            Select::make('level')
                                ->options([
                                    'beginner' => 'Beginner',
                                    'intermediate' => 'Intermediate',
                                    'advanced' => 'Advanced',
                                    'expert' => 'Expert',
                                ]),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Add Skill')
                        ->columnSpanFull(),

                    Textarea::make('working_days')
                        ->label('Working Days'),

                    TextInput::make('working_hours')
                        ->label('Working Hours'),

                    Toggle::make('on_call_required')
                        ->label('On-call Support Required'),

                    Textarea::make('performance_goals')
                        ->label('Performance Goals / KPIs')
                        ->columnSpanFull(),

                    Textarea::make('additional_notes')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Assigned Projects')
                ->schema([
                    Repeater::make('projects')
                        ->relationship()
                        ->schema([
                            TextInput::make('project_name')
                                ->required(),

                            TextInput::make('role'),

                            Select::make('status')
                                ->options([
                                    'active' => 'Active',
                                    'completed' => 'Completed',
                                    'paused' => 'Paused',
                                ])
                                ->default('active'),

                            DatePicker::make('assigned_at'),

                            DatePicker::make('ended_at'),

                            Textarea::make('notes')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Assign Project')
                        ->columnSpanFull(),
                ]),

            Section::make('Salary & Statutory Details')
                ->schema([
                    TextInput::make('basic_salary')
                        ->numeric()
                        ->prefix('LKR')
                        ->required(),

                    TextInput::make('fixed_allowance')
                        ->numeric()
                        ->prefix('LKR')
                        ->default(0),

                    TextInput::make('other_allowances')
                        ->numeric()
                        ->prefix('LKR')
                        ->default(0),

                    TextInput::make('standard_deduction')
                        ->numeric()
                        ->prefix('LKR')
                        ->default(0),

                    Select::make('salary_payment_method')
                        ->options([
                            'bank_transfer' => 'Bank Transfer',
                            'cash' => 'Cash',
                            'cheque' => 'Cheque',
                        ]),

                    TextInput::make('bank_name'),

                    TextInput::make('bank_branch'),

                    TextInput::make('bank_account_holder'),

                    TextInput::make('bank_account_number')
                        ->password()
                        ->revealable(),

                    TextInput::make('epf_membership_no'),

                    TextInput::make('etf_details'),

                    TextInput::make('tax_identification_no'),

                    TextInput::make('allowance')
                        ->label('Legacy Allowance')
                        ->numeric()
                        ->default(0)
                        ->helperText('Kept for compatibility with the existing Payroll structure.'),

                    TextInput::make('deduction')
                        ->label('Legacy Deduction')
                        ->numeric()
                        ->default(0)
                        ->helperText('Kept for compatibility with the existing Payroll structure.'),
                ])
                ->columns(2),

            Section::make('Emergency Contact')
                ->schema([
                    Repeater::make('emergencyContacts')
                        ->relationship()
                        ->schema([
                            TextInput::make('name')
                                ->required(),

                            TextInput::make('relationship'),

                            TextInput::make('phone')
                                ->required(),

                            TextInput::make('alternative_phone'),

                            Textarea::make('address')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Add Emergency Contact')
                        ->columnSpanFull(),
                ]),

            Section::make('Documents')
                ->schema([
                    Repeater::make('documents')
                        ->relationship()
                        ->schema([
                            TextInput::make('title')
                                ->required(),

                            Select::make('document_type')
                                ->options([
                                    'nic_passport' => 'NIC / Passport',
                                    'appointment_letter' => 'Appointment Letter',
                                    'employment_agreement' => 'Employment Agreement',
                                    'cv' => 'CV',
                                    'education_certificate' => 'Educational Certificate',
                                    'bank_confirmation' => 'Bank Account Confirmation',
                                    'epf_document' => 'EPF Document',
                                    'other' => 'Other',
                                ])
                                ->required(),

                            FileUpload::make('file_path')
                                ->label('Document')
                                ->disk('public')
                                ->directory('employees/documents')
                                ->required(),

                            DatePicker::make('uploaded_at')
                                ->default(now()),

                            DatePicker::make('expiry_date'),

                            Textarea::make('notes')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Add Document')
                        ->columnSpanFull(),
                ]),

            Section::make('System Account')
                ->schema([
                    Toggle::make('system_account_enabled')
                        ->label('Employee Has Admin-System Access')
                        ->live(),

                    Select::make('user_id')
                        ->label('Connected User Account')
                        ->options(
                            fn ($record) => User::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->visible(fn ($get) => (bool) $get('system_account_enabled'))
                        ->helperText('Employee profile and system User account remain separate.'),

                    TextInput::make('email')
                        ->label('Login Email')
                        ->email()
                        ->visible(fn ($get) => (bool) $get('system_account_enabled'))
                        ->helperText('The connected User account controls actual login access.')
                        ->disabled(),
                ])
                ->columns(2),
                ])
        ->columns([
            'default' => 1,
            'xl' => 2,
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_no')
                    ->label('Employee No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('designation')
                    ->label('Designation')
                    ->searchable(),

                TextColumn::make('department')
                    ->searchable(),

                TextColumn::make('join_date')
                    ->date(),

                TextColumn::make('basic_salary')
                    ->money('LKR'),

                TextColumn::make('status')
                    ->badge(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
    
    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Employee Overview')
                ->schema([
                    TextEntry::make('employee_no')
                        ->label('Employee Number'),

                    TextEntry::make('name')
                        ->label('Full Name'),

                    TextEntry::make('preferred_name')
                        ->label('Preferred Name')
                        ->placeholder('-'),

                    TextEntry::make('designation')
                        ->label('Designation')
                        ->placeholder('-'),

                    TextEntry::make('department')
                        ->label('Department')
                        ->placeholder('-'),

                    TextEntry::make('status')
                        ->badge(),

                    TextEntry::make('employment_type')
                        ->label('Employment Type')
                        ->placeholder('-'),

                    TextEntry::make('join_date')
                        ->label('Joining Date')
                        ->date()
                        ->placeholder('-'),

                    TextEntry::make('basic_salary')
                        ->label('Basic Salary')
                        ->money('LKR'),

                    TextEntry::make('reportingManager.name')
                        ->label('Reporting Manager')
                        ->placeholder('-'),
                ])
                ->columns(2),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployee::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
            'view' => Pages\ViewEmployee::route('/{record}'),
        ];
    }
}