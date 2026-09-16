<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Review = 'review';
    case Revision = 'revision';
    case Published = 'published';
    case Archived = 'archived';
}
