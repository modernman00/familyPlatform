@extends('baseBulma')

@section('title','About Us')
@section('data-page-id', 'aboutUs')

@section('content')

  <img src={{ getenv('IMG_CONTRACT2')}} alt="logo" class="mb-4 form__login__logo">

<h1>About the MyFamily platform </h1>

<p>
  We have developed a platform specifically designed for families and associates to connect and keep our family tree updated. This platform combines the features of social media sites with a focus on family connections. Here are the key features:
</p>
<p>
  <strong>Family Profiles:</strong> Each family member can create their own profile, providing information such as name, date of birth, and relationship to the family. Profiles can include profile pictures and additional details about their lives.
</p>
<p>
  <strong>Family Tree:</strong> The platform includes a comprehensive family tree feature where users can visualize and explore their family connections. 
  {{-- It allows you to add new family members, link relationships, and update information as the family grows. --}}
</p>
<p>
  <strong>News Feed:</strong> The news feed provides a central hub for family members and associates to share updates, photos, and milestones. It enables everyone to stay connected and informed about important events and activities happening within the family.
</p>
<p>
  <strong>Privacy Controls:</strong> Privacy is a top priority, and our platform includes robust privacy controls. Users can choose to share specific information only with selected family members or groups, ensuring that sensitive information remains within the family circle.
</p>
<p>
  <strong>Messaging and Notifications:</strong> The platform includes a messaging system that allows users to communicate with each other privately. Additionally, notifications keep users informed about important updates, new family members, and upcoming events.
</p>
<p>
  <strong>Events and Calendar:</strong> Users can create and manage family events, such as reunions, birthdays, anniversaries, and more. The platform's calendar feature helps everyone stay organized and reminds them of important dates.
</p>
<p>
  <strong>Photo Sharing:</strong> The platform offers a dedicated space for users to upload and share family photos. It preserves precious memories and allows family members to reminisce and celebrate moments together.
</p>
<p>
  <strong>Collaboration Tools:</strong> Our platform includes collaborative features such as document sharing and task management, enabling family members to work together on shared projects or plan family-related activities.
</p>
<p>
  We believe that this platform will greatly facilitate family connections and ensure that our family tree remains updated and accessible to all members and associates.
</p>






<br>




@endsection
