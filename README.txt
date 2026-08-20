CROW CRUD PACK

This ZIP contains replacement Filament resources with:
- Create button
- Edit action
- Delete action with confirmation
- Existing list/search/sort behavior
- Quotation Accept / Reject / Convert to Invoice actions
- Quotation and Invoice numbering pages are included as normal CRUD pages; if your current project has custom numbering page files, keep those custom Create pages.

IMPORTANT:
Because your current project already contains the Crow starter, make a backup first.

From:
D:\crowlk projects\crow-business

1. Back up:
   Copy-Item ".\app\Filament\Resources" ".\app\Filament\Resources.backup" -Recurse -Force

2. Extract this ZIP.

3. Copy the app folder contents into the project:
   Copy-Item "EXTRACTED_PATH\app\Filament\Resources\*" ".\app\Filament\Resources\" -Recurse -Force

4. Clear cache:
   php artisan optimize:clear

5. Start:
   php artisan serve

Each module now has Create/Edit/Delete. Quotation additionally has Accept, Reject and Convert to Invoice.

NOTE:
This pack is intended for the starter schema already installed. If you have customized a resource, merge that resource instead of overwriting it.
