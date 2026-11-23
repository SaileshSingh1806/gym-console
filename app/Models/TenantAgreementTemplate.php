<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantAgreementTemplate extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'version',
        'title',
        'is_active',
        'open_terrace_enabled',
        'terms_sections',
        'health_questions',
        'published_at',
        'published_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'is_active' => 'boolean',
        'open_terrace_enabled' => 'boolean',
        'terms_sections' => 'array',
        'health_questions' => 'array',
        'published_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function agreements(): HasMany
    {
        return $this->hasMany(MemberAgreement::class, 'agreement_template_id');
    }

    /**
     * Get default structured health questions (PAR-Q).
     */
    public static function getDefaultHealthQuestions(): array
    {
        return [
            [
                'id' => 'heart_condition',
                'question' => 'Do you have, or have you ever had, any heart condition, high blood pressure, or cardiovascular disorder?',
                'category' => 'Cardiovascular',
            ],
            [
                'id' => 'injuries_surgery',
                'question' => 'Have you experienced any bone, joint, spinal injury, major surgery, or physical limitations in the past 12 months?',
                'category' => 'Musculoskeletal',
            ],
            [
                'id' => 'asthma_breathing',
                'question' => 'Do you suffer from asthma, chronic bronchitis, shortness of breath, or any respiratory difficulty during exertion?',
                'category' => 'Respiratory',
            ],
            [
                'id' => 'dizziness_chest_pain',
                'question' => 'Have you ever felt chest pain, faintness, or severe dizziness while performing physical exercise or training?',
                'category' => 'Symptoms',
            ],
            [
                'id' => 'medications',
                'question' => 'Are you currently taking any prescription medication or undergoing treatment that may affect your ability to exercise?',
                'category' => 'Medical History',
            ],
            [
                'id' => 'medical_advice',
                'question' => 'Has a doctor or medical professional ever advised you against undertaking gym workouts or strenuous physical activity?',
                'category' => 'Medical Advice',
            ],
            [
                'id' => 'other_conditions',
                'question' => 'Do you have any other illness, medical condition (e.g. diabetes, epilepsy, hernia, pregnancy), or concern affecting workout safety?',
                'category' => 'General Health',
            ],
        ];
    }

    /**
     * Get default structured Terms & Conditions sections.
     */
    public static function getDefaultTermsSections(bool $includeTerrace = false): array
    {
        $sections = [
            [
                'key' => 'membership_payments',
                'title' => 'Membership & Payments',
                'summary' => 'Rules governing membership tenure, fee non-refundability, non-transferability, and clearing of outstanding dues.',
                'clauses' => [
                    '1. Fees once paid are strictly non-refundable and non-transferable under any circumstances, including personal relocation or non-usage.',
                    '2. Membership is valid exclusively for the registered individual and the specified contract period. Sharing of access passes or credentials is prohibited.',
                    '3. All membership packages and personal training sessions must be utilized strictly within the stipulated validity period; unutilized sessions or duration automatically lapse upon expiry.',
                    '4. Any unpaid dues or split-payment balances must be cleared on or before the agreed due date to maintain uninterrupted access to the facility.',
                ],
            ],
            [
                'key' => 'gym_discipline',
                'title' => 'Gym Rules & Discipline',
                'summary' => 'Code of conduct, mandatory workout towels, clean indoor shoes, locker hygiene, and anti-harassment policies.',
                'clauses' => [
                    '5. Members are required to carry clean indoor athletic footwear and a personal workout towel at all times on the gym floor.',
                    '6. Lockers are provided solely for temporary daily use during workout sessions. Overnight storage is not permitted, and management reserves the right to clear unattended lockers.',
                    '7. Members are solely responsible for their personal belongings, cash, and valuables. Management accepts no liability for loss, theft, or damage within the facility or lockers.',
                    '8. Zero tolerance is strictly enforced against misconduct, harassment, aggressive behavior, or violation of gym etiquette, resulting in immediate membership termination without refund.',
                ],
            ],
            [
                'key' => 'equipment_safety',
                'title' => 'Equipment & Safety',
                'summary' => 'Proper handling of gym machinery, free weights re-racking, safety precautions, and personal health declarations.',
                'clauses' => [
                    '9. Gym equipment, dumbbells, barbells, and machines must be handled with care and safely returned to their designated racks after each set.',
                    '10. Deliberate dropping of weights, equipment slam, or misuse resulting in damage will be charged to the member at replacement cost.',
                    '11. Members confirm they are medically fit for exercise. Management accepts no liability for injuries, strains, or medical emergencies arising from ordinary inherent risks of physical activity.',
                    '12. Management reserves the right to adjust facility operating hours, maintain equipment, and enforce updated safety and hygiene protocols.',
                ],
            ],
            [
                'key' => 'personal_training',
                'title' => 'Personal Training & Instruction',
                'summary' => 'Guidelines regarding certified gym trainers, external unauthorized coaching restrictions, and training session conduct.',
                'clauses' => [
                    '13. Personal training instruction on the gym floor is restricted exclusively to authorized, certified gym staff. Commercial personal training by freelance members is strictly prohibited.',
                    '14. Members receiving personal training must follow trainer guidance, notify instructors of discomfort, and cancel or reschedule sessions at least 12 hours in advance.',
                ],
            ],
            [
                'key' => 'facilities_restrictions',
                'title' => 'Additional Facilities & Operational Restrictions',
                'summary' => 'Usage of steam/sauna, shower areas, photography rules, visitor restrictions, and compliance with staff directives.',
                'clauses' => [
                    '15. Use of steam, sauna, and shower amenities is subject to scheduled facility timings, hygiene standards, and health guidelines.',
                    '16. Photography and videography must respect fellow members\' privacy; filming other gym members without their explicit permission is strictly prohibited.',
                    '17. Non-member visitors or children are not allowed on the workout floor without prior written approval from Gym Management.',
                ],
            ],
        ];

        if ($includeTerrace) {
            $sections[] = [
                'key' => 'open_terrace_rules',
                'title' => 'Open Terrace Access & Safety Rules',
                'summary' => 'Strict authorization requirements, weather protocols, safety restrictions, and management discretion for elevated terrace workout zones.',
                'clauses' => [
                    '27. Access to the Open Terrace Workout Area is strictly restricted and permitted only with prior authorization from Gym Management. Members are not entitled to automatic terrace access as part of their membership.',
                    '28. Unauthorized entry, use of terrace facilities without permission, or entry during restricted hours is strictly prohibited and may result in immediate suspension or termination of membership.',
                    '29. Members must strictly follow all safety instructions. Climbing or leaning on railings, entering restricted areas, performing dangerous activities, or misusing equipment is strictly prohibited.',
                    '30. Gym Management reserves the right to restrict, suspend, or revoke terrace access at any time due to safety concerns, weather conditions, maintenance, misconduct, or operational requirements. Members must immediately comply with such instructions.',
                ],
            ];
        }

        return $sections;
    }
}
