@extends('layouts.front')
@section('content')

<section class="user-dashbord">
    <div class="container">
      <div class="row">
        @include('includes.user-dashboard-sidebar')
                <div class="col-lg-8">
                    <div class="user-profile-details">
                        <div class="account-info">
                            <div class="header-area">
                                <h4 class="title">
                                    {{ $langg->lang1101 }}
                                </h4>
                            </div>
                            <div class="edit-info-area">
                                <div class="body">
                                    <div class="edit-info-area-form">
                                        <div class="gocover" style="background: url({{ asset('assets/images/'.$gs->loader) }}) no-repeat scroll center center rgba(45, 45, 45, 0.5);">
                                        </div>
                                        <div class="">
                                            <strong>Referal URL: </strong>
                                            </br> 
                                            {{url('/')}}/refer?name={{$user['name']}}&token_id={{$user['refer_code']}}
                                        </div>

                                        </br>
                                        @php 
                                        if(isset($users)) {
                                        @endphp
                                        <div>
                                            <strong>All Refer Users:</strong>
                                                </br>
                                                </br>

                                            @php 
                                                if(isset($users) && !empty($users)) {
                                                    foreach($users as $key => $val){
                                                        echo isset($val['name'])? $val['name'] : '';
                                                    echo "</br>";
                                                    }
                                                }       
                                            @endphp
                                        </div>
                                        @php
                                            }

                                            @endphp
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
      </div>
    </div>
  </section>

@endsection