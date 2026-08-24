<?php

namespace App\Filament\Resources\EmployeeResource\Infolists;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class EmployeeInfolist
{
    public static function make(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Overview')
                ->schema([
                    ImageEntry::make('profile_photo')
                        ->disk('public')
                        ->circular(),

                    TextEntry::make('employee_no')
                        ->label('Employee Number'),

                    TextEntry::make('name')
                        ->label('Full Name'),

                    TextEntry::make('preferred_name'),

                    TextEntry::make('designation'),

                    TextEntry::make('department'),

                    TextEntry::make('reportingManager.name')
                        ->label('Reporting Manager'),

                    TextEntry::make('join_date')
                        ->date(),

                    TextEntry::make('employment_type')
                        ->badge(),

                    TextEntry::make('status')
                        ->badge(),

                    TextEntry::make('basic_salary')
                        ->money('LKR'),
                ])
                ->columns(3),

            Section::make('Employment')
                ->schema([
                    TextEntry::make('status')->badge(),
                    TextEntry::make('employment_type'),
                    TextEntry::make('department'),
                    TextEntry::make('designation'),
                    TextEntry::make('work_location'),
                    TextEntry::make('work_mode'),
                    TextEntry::make('join_date')->date(),
                    TextEntry::make('probation_start_date')->date(),
                    TextEntry::make('probation_end_date')->date(),
                    TextEntry::make('confirmation_date')->date(),
                    TextEntry::make('contract_start_date')->date(),
                    TextEntry::make('contract_end_date')->date(),
                    TextEntry::make('resignation_date')->date(),
                    TextEntry::make('termination_date')->date(),
                    TextEntry::make('reason_for_leaving'),
                ])
                ->columns(3),

            Section::make('Job Role')
                ->schema([
                    TextEntry::make('job_title'),
                    TextEntry::make('access_level'),
                    TextEntry::make('job_description')->columnSpanFull(),
                    TextEntry::make('working_days'),
                    TextEntry::make('working_hours'),
                    TextEntry::make('on_call_required')->boolean(),
                    TextEntry::make('performance_goals')->columnSpanFull(),
                    TextEntry::make('additional_notes')->columnSpanFull(),

                    RepeatableEntry::make('responsibilities')
                        ->schema([
                            TextEntry::make('title'),
                        ])
                        ->columnSpanFull(),

                    RepeatableEntry::make('skills')
                        ->schema([
                            TextEntry::make('skill'),
                            TextEntry::make('level'),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),

                    RepeatableEntry::make('projects')
                        ->schema([
                            TextEntry::make('project_name'),
                            TextEntry::make('role'),
                            TextEntry::make('status')->badge(),
                            TextEntry::make('assigned_at')->date(),
                            TextEntry::make('ended_at')->date(),
                            TextEntry::make('notes'),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Salary & Statutory')
                ->schema([
                    TextEntry::make('basic_salary')->money('LKR'),
                    TextEntry::make('fixed_allowance')->money('LKR'),
                    TextEntry::make('other_allowances')->money('LKR'),
                    TextEntry::make('standard_deduction')->money('LKR'),
                    TextEntry::make('salary_payment_method'),
                    TextEntry::make('bank_name'),
                    TextEntry::make('bank_branch'),
                    TextEntry::make('bank_account_holder'),
                    TextEntry::make('bank_account_number')
                        ->mask(fn () => '********'),
                    TextEntry::make('epf_membership_no'),
                    TextEntry::make('etf_details'),
                    TextEntry::make('tax_identification_no'),
                ])
                ->columns(3),

            Section::make('Emergency Contact')
                ->schema([
                    RepeatableEntry::make('emergencyContacts')
                        ->schema([
                            TextEntry::make('name'),
                            TextEntry::make('relationship'),
                            TextEntry::make('phone'),
                            TextEntry::make('alternative_phone'),
                            TextEntry::make('address'),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ]),

            Section::make('Documents')
                ->schema([
                    RepeatableEntry::make('documents')
                        ->schema([
                            TextEntry::make('title'),
                            TextEntry::make('document_type'),
                            TextEntry::make('uploaded_at')->date(),
                            TextEntry::make('expiry_date')->date(),
                            TextEntry::make('file_path'),
                            TextEntry::make('notes'),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ]),

            Section::make('System Access')
                ->schema([
                    TextEntry::make('user.name')
                        ->label('System User'),

                    TextEntry::make('user.email')
                        ->label('Login Email'),

                    TextEntry::make('system_account_enabled')
                        ->boolean(),

                    TextEntry::make('user.email_verified_at')
                        ->label('Email Verified')
                        ->dateTime(),
                ])
                ->columns(2),
        ]);
    }
}