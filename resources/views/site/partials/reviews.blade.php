@if($reviews->isNotEmpty())
<div class="reviews-head">
    @if($reviewSummary['count'] > 0)
    <div class="score-card">
        <span class="score-value">{{ number_format($reviewSummary['average'], 1) }}</span>
        <div>
            <span class="score-label">{{ __('site.score_word_' . \App\Support\Seo::scoreWord($reviewSummary['average'])) }}</span>
            <span class="score-meta">{{ trans_choice('site.reviews_count', $reviewSummary['count'], ['count' => $reviewSummary['count']]) }} · {{ __('site.out_of_10') }}</span>
        </div>
    </div>
    @endif
    <div class="slider-controls">
        <button type="button" class="icon-btn" data-slider-prev="reviews-track" aria-label="{{ __('site.previous') }}">@include('site.partials.icon', ['name' => 'chevron-left'])</button>
        <button type="button" class="icon-btn" data-slider-next="reviews-track" aria-label="{{ __('site.next') }}">@include('site.partials.icon', ['name' => 'chevron-right'])</button>
    </div>
</div>

<div class="reviews-track" id="reviews-track" tabindex="0" aria-label="{{ __('messages.GuestReviews') }}">
    @foreach($reviews as $review)
    <figure class="review-card">
        <div class="review-top">
            <span class="review-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($review->firstName, 0, 1)) }}</span>
            <div class="review-who">
                <span class="review-name">{{ $review->firstName }} <span class="review-flag">{{ \App\Support\Seo::flag($review->countryCode) }}</span></span>
                @if($review->reviewDate)
                <time class="review-date" datetime="{{ substr($review->reviewDate, 0, 10) }}">{{ \Carbon\Carbon::parse($review->reviewDate)->locale(app()->getLocale())->isoFormat('MMMM YYYY') }}</time>
                @endif
            </div>
            @if($review->ratingNumber)
            <span class="review-score" aria-label="{{ __('site.rated', ['score' => (float) $review->ratingNumber]) }}">{{ rtrim(rtrim(number_format((float) $review->ratingNumber, 1), '0'), '.') }}</span>
            @endif
        </div>
        <blockquote>
            @if($review->title)
            <p class="review-title">“{{ $review->title }}”</p>
            @endif
            @if($review->positiveComment)
            <p class="review-text">{{ $review->positiveComment }}</p>
            @endif
        </blockquote>
    </figure>
    @endforeach
</div>
@endif
