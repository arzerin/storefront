<?php

use App\Models\Admin;
use App\Models\MediaAsset;
use App\Models\SiteBlock;
use App\Models\SitePage;
use App\Support\SiteBuilder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('creates a draft page, saves ordered sections, previews and publishes it', function () {
    $this->get(route('admin.site-builder.index'))->assertRedirect(route('admin.login'));
    $this->actingAs(Admin::create(['name'=>'Owner','email'=>'builder@example.com','password'=>'test-password']), 'admin');
    $this->get(route('admin.site-builder.index'))->assertOk();
    $this->get(route('admin.site-builder.create'))->assertOk();
    $this->get(route('admin.site-builder.themes'))->assertOk()->assertSee('Collection campaign');
    $this->post(route('admin.site-builder.store'), ['title'=>'Autumn Edit','slug'=>'autumn-edit','template'=>'campaign'])->assertRedirect();
    $page = SitePage::where('slug', 'autumn-edit')->firstOrFail();
    $this->get('/autumn-edit')->assertNotFound();
    $this->get(route('admin.site-builder.edit', $page))->assertOk()->assertSee('Page sections');
    $sections = [
        ['id'=>'first','type'=>'faq','data'=>['headline'=>'Shipping questions','items'=>[['question'=>'When?','answer'=>'Tomorrow.']]]],
        ['id'=>'second','type'=>'hero','data'=>['headline'=>'Autumn layers','body'=>'Made to move.']],
    ];
    $this->put(route('admin.site-builder.update', $page), ['title'=>'Autumn Edit','slug'=>'autumn-edit','template'=>'campaign','sections_json'=>json_encode($sections),'intent'=>'preview'])->assertRedirect(route('admin.site-builder.preview', $page));
    $this->get(route('admin.site-builder.preview', $page))->assertOk()->assertSee('Autumn layers')->assertSee('Shipping questions');
    $this->post(route('admin.site-builder.publish', $page))->assertRedirect();
    $this->get('/autumn-edit')->assertOk()->assertSee('Autumn layers')->assertSee('Tomorrow.')->assertSee('ayra-builder-template-campaign');
    $page->refresh();
    expect(array_column($page->publishedSections(), 'type'))->toBe(['faq','hero']);
    $this->put(route('admin.site-builder.update', $page), ['title'=>'Autumn Edit','slug'=>'autumn-edit','template'=>'campaign','sections_json'=>json_encode([['type'=>'hero','data'=>['headline'=>'New draft']]]),'intent'=>'save'])->assertRedirect();
    $this->get('/autumn-edit')->assertSee('Autumn layers')->assertDontSee('New draft');
    $this->post(route('admin.site-builder.restore', [$page, $page->publishedRevision]))->assertRedirect();
    $this->get('/autumn-edit')->assertSee('Autumn layers');
});

it('rejects reserved slugs and unsafe page content', function () {
    $this->actingAs(Admin::create(['name'=>'Owner','email'=>'safe-builder@example.com','password'=>'test-password']), 'admin');
    $this->post(route('admin.site-builder.store'), ['title'=>'Bad','slug'=>'login','template'=>'editorial'])->assertSessionHasErrors('slug');
    $page = SitePage::create(['title'=>'Test','slug'=>'test-page','template'=>'editorial','draft_sections'=>SiteBuilder::starterSections('editorial')]);
    $this->put(route('admin.site-builder.update', $page), ['title'=>'Test','slug'=>'test-page','template'=>'editorial','sections_json'=>json_encode([['type'=>'hero','data'=>['headline'=>'Hello','button_label'=>'Click','button_url'=>'javascript:alert(1)']]])])->assertSessionHasErrors();
    $this->get('/login')->assertOk();
});

it('uploads images for both page sections and blocks with reusable alt text', function () {
    Storage::fake('public');
    $this->actingAs(Admin::create(['name'=>'Owner','email'=>'media@example.com','password'=>'test-password']), 'admin');
    $this->get(route('admin.media-library.index'))->assertOk();
    $this->post(route('admin.media-library.store'), ['image'=>UploadedFile::fake()->image('linen.jpg'), 'alt_text'=>'Linen coat on a model'])->assertRedirect();
    $asset = MediaAsset::where('original_name','linen.jpg')->firstOrFail();
    Storage::disk('public')->assertExists(substr($asset->path, strlen('/storage/')));
    $this->post(route('admin.blocks.store'), ['title'=>'Lookbook','body'=>'New season','region'=>'home_before_collections','sort_order'=>0,'is_enabled'=>1,'media_asset_id'=>$asset->id])->assertRedirect();
    $this->get(route('home'))->assertSee('Linen coat on a model')->assertSee($asset->path);
    $this->delete(route('admin.media-library.destroy', $asset))->assertSessionHasErrors('image');
    $this->put(route('admin.media-library.update', $asset), ['alt_text'=>'Updated coat description'])->assertRedirect();
    $this->get(route('home'))->assertSee('Updated coat description');
});

it('renders the builder homepage and protects its draft preview', function () {
    $page = SitePage::create(['title'=>'Home page','slug'=>'home','template'=>'editorial','is_home'=>true,'draft_sections'=>SiteBuilder::defaultHomeSections()]);
    $revision = $page->revisions()->create(['title'=>$page->title,'template'=>$page->template,'sections'=>$page->draft_sections,'published_at'=>now()]);
    $page->update(['published_revision_id'=>$revision->id]);
    $this->get(route('home'))->assertOk()->assertSee('Made for how you move.');
    $this->get(route('admin.site-builder.preview', $page))->assertRedirect(route('admin.login'));
    $this->actingAs(Admin::create(['name'=>'Owner','email'=>'home-builder@example.com','password'=>'test-password']), 'admin');
    $this->get(route('admin.site-builder.preview', $page))->assertOk()->assertSee('Draft preview');
});

it('uses selected library images on published pages and keeps them for rollback', function () {
    $this->actingAs(Admin::create(['name'=>'Owner','email'=>'media-page@example.com','password'=>'test-password']), 'admin');
    $asset = MediaAsset::create(['path'=>'/media/ayra/hero.webp','original_name'=>'hero.webp','alt_text'=>'Green coat editorial','mime_type'=>'image/webp','size_bytes'=>100]);
    $page = SitePage::create(['title'=>'About','slug'=>'about','template'=>'editorial','draft_sections'=>[['type'=>'hero','data'=>['headline'=>'Our story','media_asset_id'=>$asset->id]]]]);
    $this->post(route('admin.site-builder.publish', $page))->assertRedirect();
    $this->get('/about')->assertOk()->assertSee('Green coat editorial')->assertSee('Our story');
    $this->delete(route('admin.media-library.destroy', $asset))->assertSessionHasErrors('image');
});

it('keeps site settings connected to the original homepage hero', function () {
    $page = SitePage::create(['title'=>'Home page','slug'=>'home','template'=>'editorial','is_home'=>true,'draft_sections'=>SiteBuilder::defaultHomeSections()]);
    $revision = $page->revisions()->create(['title'=>$page->title,'template'=>$page->template,'sections'=>$page->draft_sections,'published_at'=>now()]);
    $page->update(['published_revision_id'=>$revision->id]);
    \App\Models\SiteSetting::updateOrCreate(['key'=>'hero_headline'], ['value'=>'Fresh from Ayra']);
    $this->get(route('home'))->assertOk()->assertSee('Fresh from Ayra');
});
