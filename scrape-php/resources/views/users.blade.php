<div>

<h1>Users list</h1>

  <table border="1">
    <tr>
      <td>name</td>
      <td>email</td>
      <td>password</td>
    </tr>
    @foreach($users as $user)
    <tr>
      <td>{{$user->name}}</td>
      <td>{{$user->email}}</td>
      <td>{{$user->password}}</td>
    </tr> 
    @endforeach
  </table>

</div>