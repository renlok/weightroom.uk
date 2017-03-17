<script src="{{ asset('js/jCollapsible.js') }}"></script>

<script>
$(document).ready(function(){
    $('.object_comments').collapsible({
        xoffset:'-30',
        symbolhide:'[-]',
        symbolshow:'[+]'
    @if ($commenting)
        , defaulthide:false
    @endif
    });
    $('.reply').click(function() {
        var element = $(this).parent().parent().find(".comment-reply-box").first();
        if ( element.is( ":hidden" ) ) {
            element.slideDown("slow");
        } else {
            element.slideUp("slow");
        }
        return false;
    });
    $('.delete').click(function() {
        var comment_id = $(this).attr('c-id');
        var element = $('#c' + comment_id).text('[Deleted]');
        $.ajax({
            url: "{{ route('deleteComment', ['comment_id' => ':cid']) }}".replace(':cid', comment_id),
            type: 'GET',
            dataType: 'json',
            cache: false
        });
        return false;
    });
});
</script>
