@props(['course', 'compact' => false])

<article class="course-card {{ $compact ? 'course-card-compact' : '' }}">
    <a href="{{ route('courses.show', $course->slug) }}" class="course-thumb"
       style="--cat-color: {{ $course->category->color ?? '#4f46e5' }}">
        @if($course->image_url)
            <img src="{{ $course->image_url }}" alt="{{ $course->title }}" class="course-thumb-img" loading="lazy">
        @else
            <span class="course-thumb-icon">{{ $course->category->icon ?? '💻' }}</span>
        @endif
        @if($course->discount_percent)
            <span class="course-thumb-badge">-{{ $course->discount_percent }}%</span>
        @endif
    </a>
    <div class="course-body">
        <div class="course-meta-top">
            <a href="{{ route('categories.show', $course->category->slug) }}" class="course-category">{{ $course->category->name }}</a>
            <span class="course-level">{{ $course->level }}</span>
        </div>

        <h3 class="course-title">
            <a href="{{ route('courses.show', $course->slug) }}">{{ $course->title }}</a>
        </h3>

        <p class="course-instructor">👤 {{ $course->instructor ?? 'Instruktur Farafi' }}</p>

        <div class="course-stats">
            <span>⭐ {{ number_format($course->rating, 1) }}</span>
            <span>🎬 {{ $course->lessons }} materi</span>
            <span>⏱ {{ $course->duration_hours }} jam</span>
        </div>

        <div class="course-footer">
            <div class="course-price">
                @if($course->discount_price)
                    <span class="price-old">Rp{{ number_format($course->price, 0, ',', '.') }}</span>
                @endif
                <span class="price-now">Rp{{ number_format($course->final_price, 0, ',', '.') }}</span>
            </div>
            <form action="{{ route('cart.store') }}" method="POST">
                @csrf
                <input type="hidden" name="course_id" value="{{ $course->id }}">
                <button type="submit" class="btn btn-add" title="Tambah ke keranjang">+ Keranjang</button>
            </form>
        </div>
    </div>
</article>
