{{ $mailbox->getReplySeparator() }}

@if (count($threads) == 1){{ __('safeemailnotifications::messages.received_new_conversation') }}@else @if ($thread->action_type == App\Thread::ACTION_TYPE_STATUS_CHANGED){{ __('safeemailnotifications::messages.marked_as_status', ['person' => $thread->getCreatedBy()->getFullName(true), 'status' => $thread->getStatusName()]) }}@elseif ($thread->action_type == App\Thread::ACTION_TYPE_USER_CHANGED)
@include('emails/user/thread_by') {{ __('safeemailnotifications::messages.assigned_to_person', ['person' => $thread->getAssigneeName(false, $user)]) }}@elseif ($thread->type == App\Thread::TYPE_NOTE){{ __('safeemailnotifications::messages.added_note_to_conversation', ['person' => $thread->getCreatedBy()->getFullName(true)]) }}@else
{{ __('safeemailnotifications::messages.replied_to_conversation', ['person' => $thread->getCreatedBy()->getFullName(true)]) }}@endif @endif #{{ $conversation->number }}

@foreach ($threads as $thread)
-----------------------------------------------------------
@if ($thread->type == App\Thread::TYPE_LINEITEM)
## {!! $thread->getActionText('', true, false, $user, view('emails/user/thread_by', ['thread' => $thread, 'user' => $user])->render()) !!}, {{ __('on :date', ['date' => App\Customer::dateFormat($thread->created_at, 'M j @ H:i').' ('.\Config::get('app.timezone').')' ]) }}
@else
@if ($thread->type == App\Thread::TYPE_NOTE)
## {{ __('safeemailnotifications::messages.added_note_to_conversation', ['person' => $thread->getCreatedBy()->getFullName(true)]) }}, {{ __('on :date', ['date' => App\Customer::dateFormat($thread->created_at, 'M j @ H:i').' ('.\Config::get('app.timezone').')' ]) }}@else
## @if ($thread->isForwarded()){{ __(':person forwarded a conversation :forward_parent_conversation_number', ['person' => $thread->getCreatedBy()->getFullName(true), 'forward_parent_conversation_number' => '#'.$thread->getMetaFw(App\Thread::META_FORWARD_PARENT_CONVERSATION_NUMBER)]) }}@elseif ($loop->last){{ __(':person started the conversation', ['person' => $thread->getCreatedBy()->getFullName(true)]) }}@else{{ __(':person replied', ['person' => $thread->getCreatedBy()->getFullName(true)]) }}@endif, {{ __('on :date', ['date' => App\Customer::dateFormat($thread->created_at, 'M j @ H:i').' ('.\Config::get('app.timezone').')' ]) }}@endif
@if ($thread->isForward()){{ __(':person forwarded this conversation. Forwarded conversation: :forward_child_conversation_number', ['person' => ucfirst($thread->getForwardByFullName()),'forward_child_conversation_number' => '#'.$thread->getMetaFw(App\Thread::META_FORWARD_CHILD_CONVERSATION_NUMBER)]) }}

@endif
{{ __('safeemailnotifications::messages.view_conversation') }}: {{ \Eventy::filter('email_notification.conv_url', $conversation->url(), $user) }}
@endif
@endforeach

{{ __('Conversation URL') }}: {{ \Eventy::filter('email_notification.conv_url', $conversation->url(), $user) }}

{{ \Eventy::action('email_notification_text.footer_links', $mailbox, $conversation, $threads) }}

-----------------------------------------------------------

{{ $mailbox->name }}:
{{ \Eventy::filter('email_notification.mailbox_url', $mailbox->url(), $user) }}
