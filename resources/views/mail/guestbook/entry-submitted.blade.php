<x-mail::message>
# New portfolio review

A new comment or review was published automatically. You can edit, hide, or delete it from the portfolio admin.

**Name:** {{ $entry->name }}  
**Email:** {{ $entry->email }}  
@if($entry->role_or_organization)
**Role or organization:** {{ $entry->role_or_organization }}  
@endif
@if($entry->rating)
**Rating:** {{ $entry->rating }}/5  
@endif

**Message**

{{ $entry->body }}

<x-mail::button :url="route('admin.index')">
Manage review in portfolio admin
</x-mail::button>

Submitted {{ $entry->created_at?->format('M j, Y g:i A') }}.
</x-mail::message>