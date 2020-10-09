@component('mail::message')
# New Teacher Registration

@component('mail::table')
| User ID         | Name            | Email            | Phone            |
|:--------------- |:--------------- |:---------------- |:---------------- |
| {{ $userId }}   | {{ $userName }} | {{ $userEmail }} | {{ $userPhone }} |
@endcomponent

@component('mail::button', ['url' => $url])
Go to Approvals
@endcomponent

@endcomponent
