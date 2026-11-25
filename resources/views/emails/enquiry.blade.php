<!doctype html>
<html lang="en">
<body style="font-family:Arial,sans-serif;color:#111C2B;line-height:1.5">
<h2 style="margin:0 0 12px">New inquiry from {{ $enquiry->fullName() }}</h2>
<table cellpadding="6" style="border-collapse:collapse">
    <tr><td><strong>Form</strong></td><td>{{ $enquiry->form_name }}</td></tr>
    <tr><td><strong>Email</strong></td><td>{{ $enquiry->email }}</td></tr>
    <tr><td><strong>Phone</strong></td><td>{{ $enquiry->phone_e164 }}</td></tr>
    @if($enquiry->package_name)<tr><td><strong>Package</strong></td><td>{{ $enquiry->package_name }}</td></tr>@endif
    @if($enquiry->tour_type)<tr><td><strong>Tour type</strong></td><td>{{ $enquiry->tour_type }}</td></tr>@endif
    @if($enquiry->travel_date)<tr><td><strong>Travel date</strong></td><td>{{ $enquiry->travel_date }}</td></tr>@endif
    @if($enquiry->second_quote)<tr><td><strong>Second quote?</strong></td><td>{{ $enquiry->second_quote }}</td></tr>@endif
    @if($enquiry->message)<tr><td valign="top"><strong>Message</strong></td><td>{!! nl2br(e($enquiry->message)) !!}</td></tr>@endif
</table>
@if($enquiry->answers)
    <h3>Quiz answers</h3>
    <ul>
        @foreach(\App\Services\EnquiryService::answerLabels($enquiry->answers) as $k => $v)
            <li><strong>{{ str_replace('_',' ',$k) }}:</strong> {{ $v }}</li>
        @endforeach
    </ul>
@endif
<p><a href="{{ url('/admin/enquiries/'.$enquiry->id) }}">Open in admin</a></p>
</body>
</html>
