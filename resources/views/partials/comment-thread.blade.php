@php
    $likedIds = $likedIds ?? [];
    $isProject = $isProject ?? false;
    $commentContext = $commentContext ?? ($isProject ? 'project' : 'article');
@endphp
<div class="oc-comment-thread border-b oc-divide pb-6 mb-6" id="comment-thread-{{ $root->id }}">
    @include('partials.comment-node', [
        'c' => $root,
        'likedIds' => $likedIds,
        'isProject' => $isProject,
        'commentContext' => $commentContext,
        'isReply' => false,
    ])
    <div class="oc-comment__flat-replies ml-10 md:ml-14 pl-3 border-l-2 border-slate-200/80 space-y-4 mt-3">
        @foreach ($root->replies ?? [] as $reply)
            @include('partials.comment-node', [
                'c' => $reply,
                'likedIds' => $likedIds,
                'isProject' => $isProject,
                'commentContext' => $commentContext,
                'isReply' => true,
            ])
        @endforeach
    </div>
</div>
