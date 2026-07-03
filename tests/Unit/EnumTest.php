<?php

declare(strict_types=1);

use App\Domains\Activities\Enums\ActivityStatus;
use App\Domains\Evidence\Enums\EvidenceType;
use App\Domains\Projects\Enums\Priority;
use App\Domains\Stages\Enums\StageStatus;

it('los enums exponen etiquetas en español', function () {
    expect(ActivityStatus::InDevelopment->label())->toBe('En desarrollo')
        ->and(StageStatus::InReview->label())->toBe('En revisión')
        ->and(Priority::Urgent->label())->toBe('Urgente');
});

it('deduce el tipo de evidencia según el archivo', function () {
    expect(EvidenceType::fromFile('image/png', 'png'))->toBe(EvidenceType::Image)
        ->and(EvidenceType::fromFile('video/mp4', 'mp4'))->toBe(EvidenceType::Video)
        ->and(EvidenceType::fromFile('application/pdf', 'pdf'))->toBe(EvidenceType::Pdf)
        ->and(EvidenceType::fromFile('application/zip', 'zip'))->toBe(EvidenceType::Zip)
        ->and(EvidenceType::fromFile('application/msword', 'doc'))->toBe(EvidenceType::Document);
});

it('identifica los tipos basados en enlaces', function () {
    expect(EvidenceType::Link->isLinkBased())->toBeTrue()
        ->and(EvidenceType::Figma->isLinkBased())->toBeTrue()
        ->and(EvidenceType::Production->isLinkBased())->toBeTrue()
        ->and(EvidenceType::Image->isLinkBased())->toBeFalse();
});

it('options devuelve todos los casos', function () {
    expect(Priority::options())->toHaveCount(4)
        ->and(StageStatus::options())->toHaveKeys(['pending', 'in_progress', 'in_review', 'completed']);
});
