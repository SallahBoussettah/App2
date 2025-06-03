@extends('master')

@section('content')
  <div class="row">
     <div class="col-md-8">
      <h1>Page Blog</h1>
      @foreach($myPosts as $post)
         <div class="card text-center">
           <img class="card-img-top" src="{{ asset('storage/'.$post->image) }}" alt="">
           <div class="card-body">
           <h4 class="card-title">{{ $post->title }}</h4>
           <p class="card-text">{{ str_limit($post->excerpt, 200)}}</p>
           </div>
         </div>
      @endforeach
    </div>

      <div class="col-md-4">
          Lorem ipsum dolor, sit amet consectetur adipisicing elit. Dignissimos iste labore ipsam aliquam, minus adipisci totam accusamus blanditiis asperiores cupiditate sit error iure repudiandae laboriosam rem consectetur quo in natus!
      </div>
  </div>

  <div>
      {{ $myPosts->links() }}
  </div>


@endsection
