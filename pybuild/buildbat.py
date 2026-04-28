# ai generated class
from pathlib import Path
import fnmatch
import shutil


class GitIgnore:
    def __init__(self, base: Path, file_name: str | None = ".gitignore"):
        self.base = base.resolve()
        self._file_name = file_name
        self._rules = list()
        self._load_gitignores()

    def _load_gitignores(self):
        if self._file_name is None:
            self.add_ignore_rule('*.iml')
            self.add_ignore_rule('.git/')
            self.add_ignore_rule('.idea/')
            return
        for directory in [self.base, *self.base.parents]:
            ignore_file = directory / self._file_name
            if not ignore_file.is_file():
                continue

            with ignore_file.open("r", encoding="utf-8") as f:
                for line in f:
                    line = line.strip()
                    if not line or line.startswith("#"):
                        continue
                    self._rules.append((directory, line))
        self.add_ignore_rule('*.iml')
        self.add_ignore_rule('.git/')
        self.add_ignore_rule('.idea/')

    def add_ignore_rule(self, rule: str):
        self._rules.append((self.base, rule))

    def should_ignore(self, path: Path) -> bool:
        path = path.resolve()
        ignored = False
        for base, pattern in self._rules:
            negate = pattern.startswith("!")
            if negate:
                pattern = pattern[1:]

            try:
                target = path.relative_to(base)
            except ValueError:
                continue

            target_str = target.as_posix()
            name = path.name

            matched = False

            # Directory rule
            if pattern.endswith("/"):
                if path.is_dir():
                    matched = fnmatch.fnmatch(target_str + "/", pattern)
            else:
                # If pattern has no slash, match anywhere in tree
                if "/" not in pattern:
                    matched = (
                            fnmatch.fnmatch(name, pattern)
                            or fnmatch.fnmatch(target_str, f"*/{pattern}")
                            or fnmatch.fnmatch(target_str, pattern)
                    )
                else:
                    matched = fnmatch.fnmatch(target_str, pattern)

            if matched:
                ignored = not negate

        return ignored

    def copy_to(self, src: Path, dst: Path) -> int:
        src = src.resolve()
        dst = dst.resolve()
        moved = int()

        def _copy_recursive(current_src: Path, current_dst: Path):
            nonlocal moved
            if self.should_ignore(current_src):
                return

            if current_src.is_dir():
                moved += 1
                current_dst.mkdir(parents=True, exist_ok=True)
                for child in current_src.iterdir():
                    _copy_recursive(child, current_dst / child.name)
            else:
                current_dst.parent.mkdir(parents=True, exist_ok=True)
                if str(current_src).replace('\\', '/').endswith('/blog/dbc/database.json'):
                    return
                moved += 1
                # if str(current_src).replace('\\', '/').endswith('/blog/dbc/database.prod.json'):
                #     print(current_src, current_dst)
                shutil.copy2(current_src, clean_prod_suffix(current_dst))

        _copy_recursive(src, dst)
        return moved

    pass


def clean_prod_suffix(path_obj):
    suffixes = path_obj.suffixes

    # Check if there are at least 2 suffixes and the second to last is '.prod'
    if len(suffixes) >= 2 and suffixes[-2] == ".prod":
        # Remove the second to last element
        new_suffixes = suffixes[:-2] + [suffixes[-1]]
        # path_obj.stem gives the filename without ANY suffixes
        return path_obj.parent / (path_obj.name.split('.')[0] + "".join(new_suffixes))

    return path_obj


pass
# def main():
#     directory = Path('..')
#     dst = Path('../piout') / 'gallery'
#     if dst.exists():
#         shutil.rmtree(dst)
#     dst.mkdir(parents=True)
#     # gitignored = GitIgnore(directory, '.buildbat-ignore')
#     gitignored = GitIgnore(directory,None)
#     gitignored.add_ignore_rule('junk')
#     gitignored.add_ignore_rule('piout')
#     gitignored.add_ignore_rule('*.iml')
#     gitignored.add_ignore_rule('*.py')
#     gitignored.add_ignore_rule('.git/')
#     gitignored.add_ignore_rule('.idea/')
#     print('moved', gitignored.copy_to(directory, dst), 'items')
#     pass
pass


def copysite(path):
    directory = Path(r'D:\var\www\BOTs') / path
    dst = Path('..') / 'piout' / directory.name
    if dst.exists():
        shutil.rmtree(dst)
    dst.mkdir(parents=True)
    # gitignored = GitIgnore(directory, '.buildbat-ignore')
    gitignored = GitIgnore(directory, None)
    gitignored.add_ignore_rule('junk')
    gitignored.add_ignore_rule('piout')
    gitignored.add_ignore_rule('*.iml')
    gitignored.add_ignore_rule('*.kra')
    gitignored.add_ignore_rule('*.py')
    gitignored.add_ignore_rule('.git/')
    gitignored.add_ignore_rule('.idea/')
    gitignored.add_ignore_rule('*backup*')
    gitignored.add_ignore_rule('comic-cropper.php')
    return gitignored.copy_to(directory, dst)


def main():
    zero = int()
    for i in ('blog',):
        zero += (one := copysite(i))
        print('moved', one, 'items in', i)
    print('moved', zero, 'items')
    pass


pass  # ini_set('display_errors', '1');

if __name__ == '__main__':
    main()
pass
