import {
  Card,
  Page,
  Layout,
  TextContainer,
  Image,
  Stack,
  Link,
  Heading,
} from "@shopify/polaris";
import { TitleBar } from "@shopify/app-bridge-react";

import { trophyImage } from "../assets";

// import { InstaauthCard } from "../components";
import { InstaauthBusinessCard } from "../components";

export default function HomePage() {
  return (
    <Page fullWidth>
      <TitleBar title="App name" primaryAction={null} />
      <Layout>
        {/* <Layout.Section oneHalf><InstaauthBusinessCard /></Layout.Section> */}
        <Layout.Section ><InstaauthBusinessCard /></Layout.Section>
      </Layout>
    </Page>
  );
}
