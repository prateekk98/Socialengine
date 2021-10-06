
<h2>Ning Import Instructions</h2>

<p>
  This Ning Import tool is designed to migrate your Ning 2 content provided by Ning's Archive Tool to SocialEngine.
  New Ning 3 does not currently support exporting, thus content from that version of Ning cannot be imported.
</p>

<br />
<br />

<?php if( !empty($this->dbHasContent) && empty($this->canResume) ): ?>
  <div class="warning">
    Your site already has content. The content will be removed from the existing installation if you use this
    import tool.
  </div>
  <br />
  <br />
<?php endif; ?>

<?php if ( !empty($this->canResume) ): ?>
  <div class="warning">
    You've already ran Ning importer. You can restart your import from where it stopped by clicking "Resume", or click
    "Start Import" to start a fresh import.
  </div>
  <button type="button" style="margin:10px 0px" id="continue" name="continue"
          onclick="window.location.href='<?php echo $this->url(array('action' => 'ning', 'bypass' => true)) ?>';return false;">
    Resume Import
  </button>
  <br />
  <br />
<?php endif; ?>
<ol style="margin-left:20px">
  <li>
    Do not create content or post anything. Having admin user is OK.
  </li>
  <li>
    Export your Ning Community using
    <a href="http://www.ning.com/help/?p=5219" target="_blank">their archive tool</a>.
    Be sure to download everything.
  </li>
  <li>
    In the export folder, check that you have these files and folders:
    <blockquote style="white-space: pre;">
        Directories: blogs/, discussions/, events/, groups/, members/, music/, notes/, photos/, videos/
        ning-members.json, ning-members-local.json,
        ning-blogs.json, ning-blogs-local.json,
        ning-discussions.json, ning-discussions-local.json,
        ning-events.json, ning-events-local.json,
        ning-groups.json, ning-groups-local.json,
        ning-music.json, ning-music-local.json,
        ning-notes.json, ning-notes-local.json,
        ning-photos.json, ning-photos-local.json,
        ning-videos.json, ning-videos-local.json,
    </blockquote>
    You can import as much or as little data as you wish, but at very least you will need ning-members.json.
  </li>
  <li>
    Log into your site using FTP and access your root SocialEngine directory (it
    appears to be "<?php echo APPLICATION_PATH ?>").  This is where the application/,
    externals/, public/ and temporary/ folders exist.
    Upload all of the JSON files and the folders of images and site content to
    the root SocialEngine folder.
  </li>
  <li>
    Use our Ning Import tool to import all your existing Ning members, content,
    and data.<br />
    <button type="button" style="margin:10px 0px" id="continue" name="continue"
            onclick="window.location.href='<?php echo $this->url(array('action' => 'ning')) ?>';return false;">
      Start Import
    </button>
  </li>
  <li>
    After import tool completes, delete all ning-related JSON files and directories. Do not delete SocialEngine files.
    Your Ning Community is now imported and you're ready to start using SocialEngine!
  </li>
</ol>

<br />

