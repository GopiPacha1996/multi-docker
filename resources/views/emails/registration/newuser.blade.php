@component('mail::message')
# New User Registration

@component('mail::table')
| Name            | Email            | Phone            |
|:--------------- |:---------------- |:---------------- |
| {{ $userName }} | {{ $userEmail }} | {{ $userPhone }} |
@endcomponent

New user has been registered to our site. 

@endcomponent
